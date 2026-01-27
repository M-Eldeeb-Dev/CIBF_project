/**
 * Leaflet Map Module
 * Interactive hall map for volunteer spot management
 */
import {
  getAllVolunteers,
  getOccupiedVolunteers,
  assignVolunteer,
  removeVolunteer,
  subscribeToVolunteers,
} from "./volunteers-service.js";
import { captureAndUploadMap } from "./map-screenshot-service.js";

// Map configuration for each hall
const HALL_MAPS = {
  1: { image: "CIPF_Map/CIBF-map-1.jpg", width: 1200, height: 1600 },
  2: { image: "CIPF_Map/CIBF-map-2.png", width: 1200, height: 1600 },
  3: { image: "CIPF_Map/CIBF-map-3.png", width: 1200, height: 1600 },
  4: { image: "CIPF_Map/CIBF-map-4.png", width: 1200, height: 1600 },
  5: { image: "CIPF_Map/CIBF-map-5.png", width: 1200, height: 1600 },
};

let map = null;
let mapContainerId = null;
let markers = {};
let currentHall = 1;
let subscription = null;
let allVolunteers = [];

// Toast notification function (uses global container if exists)
function showToast(message, type = "info") {
  const container = document.getElementById("toast-container");
  if (!container) {
    console.log(`[${type.toUpperCase()}] ${message}`);
    return;
  }
  const toast = document.createElement("div");
  toast.className = `toast ${type}`;
  const icons = { success: "✓", error: "✕", info: "ℹ" };
  toast.innerHTML = `<span>${icons[type] || ""}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add("hiding");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
window.showToast = showToast;

/**
 * Initialize the Leaflet map
 * @param {string} containerId - ID of the map container element
 * @param {number} hallId - Initial hall to display (1-5)
 */
export async function initMap(containerId, hallId = 1) {
  currentHall = hallId;
  mapContainerId = containerId;
  const config = HALL_MAPS[hallId];

  // Calculate bounds for the image
  const bounds = [
    [0, 0],
    [config.height, config.width],
  ];

  // Initialize Leaflet with Simple CRS for image overlay
  map = L.map(containerId, {
    crs: L.CRS.Simple,
    minZoom: -2,
    maxZoom: 2,
    zoomControl: true,
    attributionControl: false,
  });

  // Add the hall map as image overlay
  L.imageOverlay(config.image, bounds).addTo(map);
  map.fitBounds(bounds);

  // Load volunteers
  allVolunteers = await getAllVolunteers();

  // Load and display occupied volunteers
  await refreshMarkers();

  // Subscribe to real-time updates
  subscription = subscribeToVolunteers(handleRealtimeUpdate);

  return map;
}

/**
 * Switch to a different hall
 * @param {number} hallId - Hall ID (1-5)
 */
export async function switchHall(hallId) {
  if (!map) return;

  currentHall = hallId;
  const config = HALL_MAPS[hallId];
  const bounds = [
    [0, 0],
    [config.height, config.width],
  ];

  // Clear existing layers
  map.eachLayer((layer) => {
    if (layer instanceof L.ImageOverlay || layer instanceof L.CircleMarker) {
      map.removeLayer(layer);
    }
  });

  // Add new hall map
  L.imageOverlay(config.image, bounds).addTo(map);
  map.fitBounds(bounds);

  // Clear markers
  markers = {};

  // Reload markers for new hall
  await refreshMarkers();
}

/**
 * Refresh all markers for current hall
 */
async function refreshMarkers() {
  if (!map) return;

  // Ensure map container size is correctly calculated (fixes grey map issue)
  map.invalidateSize();

  // Clear existing markers
  Object.values(markers).forEach((marker) => map.removeLayer(marker));
  markers = {};

  // Get occupied volunteers for current hall
  const occupied = await getOccupiedVolunteers(currentHall);

  // Create markers for each occupied volunteer
  occupied.forEach((volunteer) => {
    if (volunteer.current_loc) {
      // Parse location if it contains coordinates
      const coords = parseLocationCoords(volunteer.current_loc);
      if (coords) {
        addMarker(volunteer, coords.y, coords.x, true);
      }
    }
  });
}

/**
 * Parse location string for coordinates
 * Format: "x:100,y:200" or just use predefined positions
 */
function parseLocationCoords(locString) {
  if (!locString) return null;
  const match = locString.match(/x:(\d+),y:(\d+)/);
  if (match) {
    return { x: parseInt(match[1]), y: parseInt(match[2]) };
  }
  return null;
}

/**
 * Add a marker to the map
 * @param {object} volunteer - Volunteer data (null for vacant spot)
 * @param {number} lat - Y coordinate
 * @param {number} lng - X coordinate
 * @param {boolean} isOccupied - Whether spot is occupied
 */
function addMarker(volunteer, lat, lng, isOccupied) {
  const color = isOccupied ? "#ef4444" : "#eab308"; // Red or Yellow

  const marker = L.circleMarker([lat, lng], {
    radius: 12,
    fillColor: color,
    color: "#ffffff",
    weight: 3,
    opacity: 1,
    fillOpacity: 0.9,
  }).addTo(map);

  // Store marker reference
  if (volunteer) {
    markers[volunteer.volunteerCode] = marker;

    // Add popup for occupied spot
    const popupContent = createOccupiedPopup(volunteer);
    marker.bindPopup(popupContent, { className: "custom-popup" });
  }

  return marker;
}

/**
 * Create popup HTML for occupied spot
 */
function createOccupiedPopup(volunteer) {
  return `
        <div class="text-center p-2" dir="rtl">
            <h3 class="font-bold text-lg mb-2">${volunteer.name}</h3>
            <p class="text-sm text-gray-600 mb-2">الكود: ${volunteer.volunteerCode}</p>
            <p class="text-sm text-gray-600 mb-3">المجموعة: ${volunteer.group}</p>
            ${volunteer.reason ? `<div class="bg-red-50 border border-red-100 rounded px-2 py-1 mb-3 text-xs text-red-600 font-semibold">السبب: ${volunteer.reason}</div>` : ""}
            <button 
                onclick="window.removeVolunteerFromSpot('${volunteer.volunteerCode}')"
                class="bg-red-500 text-white px-4 py-2 rounded-lg w-full hover:bg-red-600 transition">
                إزالة من الموقع
            </button>
        </div>
    `;
}

/**
 * Create popup HTML for volunteer selection
 */
/**
 * Create popup HTML for volunteer selection with search
 */
function createAssignPopup(lat, lng) {
  // Check both is_present and is_occupied for availability
  const availableVolunteers = allVolunteers.filter(
    (v) => !v.is_present && !v.is_occupied,
  );

  // Custom searchable dropdown HTML + Inline CSS for the popup
  return `
        <style>
            .vol-search-container { width: 260px; font-family: 'Cairo', sans-serif; }
            .vol-search-input { width: 100%; padding: 8px; border: 2px solid #e5e7eb; border-radius: 10px; outline: none; transition: border-color 0.2s; }
            .vol-search-input:focus { border-color: #2570d8; }
            .vol-list-box { margin-top: 10px; border: 1px solid #f3f4f6; border-radius: 10px; max-height: 180px; overflow-y: auto; background: #fff; }
            .vol-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #f9fafb; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; }
            .vol-item:hover { background: #eff6ff; }
            .vol-item.selected { background: #dbeafe; border-left: 4px solid #2570d8; }
            .vol-name { font-weight: 700; color: #1f2937; font-size: 13px; }
            .vol-code { font-family: monospace; color: #6b7280; font-size: 11px; }
            .assign-confirm-btn { margin-top: 12px; background: #10b981; color: white; padding: 10px; border-radius: 10px; font-weight: 700; width: 100%; transition: opacity 0.2s; }
            .assign-confirm-btn:disabled { background: #d1d5db; cursor: not-allowed; }
            .assign-confirm-btn:not(:disabled):hover { background: #059669; }
        </style>
        <div class="vol-search-container" dir="rtl text-right">
            <h3 class="font-bold text-lg mb-3 text-primary text-center">تعيين متطوع</h3>
            
            <div class="mb-2">
                <input type="text" 
                    id="volunteer-search" 
                    placeholder="ابحث بالاسم أو الكود..." 
                    class="vol-search-input"
                    onkeyup="window.filterVolunteerOptions(this.value)">
                
                <div class="vol-list-box" id="volunteer-list">
                    ${renderVolunteerOptions(availableVolunteers)}
                </div>
                <input type="hidden" id="selected-volunteer-code" value="">
            </div>

            <button 
                onclick="window.assignVolunteerToSpot(${lat}, ${lng})"
                id="assign-btn"
                disabled
                class="assign-confirm-btn">
                تأكيد التعيين
            </button>
        </div>
    `;
}

// Helper to render options
function renderVolunteerOptions(volunteers) {
  if (!volunteers || volunteers.length === 0)
    return '<div class="p-4 text-center text-gray-400 text-xs italic">لا يوجد متطوعين متاحين</div>';

  return volunteers
    .map(
      (v) => `
        <div 
            class="vol-item p-2"
            onclick="window.selectVolunteer('${v.volunteerCode}', this)">
            <span class="vol-name">${v.name}</span>
            <span class="vol-code">${v.volunteerCode}</span>
        </div>
    `,
    )
    .join("");
}

// Global functions for the popup interaction
window.filterVolunteerOptions = function (query) {
  const list = document.getElementById("volunteer-list");
  const q = (query || "").toLowerCase();
  const available = allVolunteers.filter(
    (v) => !v.is_present && !v.is_occupied,
  );
  const filtered = available.filter(
    (v) =>
      (v.name || "").toLowerCase().includes(q) ||
      (v.volunteerCode || "").toLowerCase().includes(q),
  );
  list.innerHTML = renderVolunteerOptions(filtered);
};

window.selectVolunteer = function (code, element) {
  // Update hidden input
  const input = document.getElementById("selected-volunteer-code");
  if (input) input.value = code;

  // Update visuals
  document
    .querySelectorAll(".vol-item")
    .forEach((el) => el.classList.remove("selected"));
  element.classList.add("selected");

  // Enable button
  const btn = document.getElementById("assign-btn");
  if (btn) {
    btn.disabled = false;
  }
};

/**
 * Handle map click to create new spot
 */
export function enableSpotCreation() {
  if (!map) return;

  map.on("click", async (e) => {
    const { lat, lng } = e.latlng;

    // Create temporary yellow marker
    const marker = addMarker(null, lat, lng, false);

    // Show volunteer selection popup
    const popupContent = createAssignPopup(lat, lng);
    marker.bindPopup(popupContent, { className: "custom-popup" }).openPopup();

    // Store temp marker reference
    markers["temp_" + Date.now()] = marker;
  });
}

/**
 * Assign volunteer to spot (called from popup)
 */
window.assignVolunteerToSpot = async function (lat, lng) {
  const volunteerCode = document.getElementById(
    "selected-volunteer-code",
  ).value;

  if (!volunteerCode) {
    showToast("الرجاء اختيار متطوع", "error");
    return;
  }

  const btn = document.getElementById("assign-btn");
  btn.textContent = "جاري التعيين...";
  btn.disabled = true;

  const location = `x:${Math.round(lng)},y:${Math.round(lat)}`;
  const success = await assignVolunteer(volunteerCode, currentHall, location);

  if (success) {
    // Refresh markers
    allVolunteers = await getAllVolunteers();
    await refreshMarkers();
    map.closePopup();

    // Auto-capture and upload map screenshot
    await autoSaveMapScreenshot();

    // Refresh page as requested
    await location.reload();
  } else {
    showToast("حدث خطأ في التعيين", "error");
    btn.textContent = "تعيين";
    btn.disabled = false;
  }
};

/**
 * Remove volunteer from spot (called from popup)
 */
window.removeVolunteerFromSpot = async function (volunteerCode) {
  // Use SweetAlert2 for confirmation
  const result = await Swal.fire({
    title: "تأكيد الإزالة",
    text: "هل أنت متأكد من إزالة المتطوع من هذا الموقع؟",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#EF4444",
    cancelButtonColor: "#6B7280",
    confirmButtonText: "نعم، إزالة",
    cancelButtonText: "إلغاء",
  });

  if (!result.isConfirmed) return;

  return proceedRemoval(volunteerCode);
};

/**
 * Handle the actual removal logic
 */
async function proceedRemoval(volunteerCode) {
  const success = await removeVolunteer(volunteerCode);

  if (success) {
    // Refresh markers
    allVolunteers = await getAllVolunteers();
    await refreshMarkers();
    map.closePopup();

    // Auto-capture and upload map screenshot
    await autoSaveMapScreenshot();

    // Show success feedback
    Swal.fire({
      icon: "success",
      title: "تمت الإزالة",
      text: "تم إزالة المتطوع من الموقع بنجاح",
      timer: 1500,
      showConfirmButton: false,
    });
  } else {
    Swal.fire("خطأ", "حدث خطأ في الإزالة", "error");
  }
}

/**
 * Handle real-time updates
 */
async function handleRealtimeUpdate(payload) {
  console.log("Realtime update received:", payload);

  // Refresh all volunteers data
  allVolunteers = await getAllVolunteers();

  // Refresh markers
  await refreshMarkers();
}

/**
 * Cleanup function
 */
export function destroyMap() {
  if (subscription) {
    subscription.unsubscribe();
  }
  if (map) {
    map.remove();
    map = null;
  }
  markers = {};
}

/**
 * Get current hall ID
 */
export function getCurrentHall() {
  return currentHall;
}

/**
 * Auto-save map screenshot after changes
 */
async function autoSaveMapScreenshot() {
  try {
    const container = document.getElementById(mapContainerId);
    if (container) {
      console.log("Auto-saving map screenshot...");
      const url = await captureAndUploadMap(container, currentHall);
      if (url) {
        console.log("Map screenshot saved:", url);
      }
    }
  } catch (error) {
    console.error("Error auto-saving map screenshot:", error);
  }
}

/**
 * Manually trigger screenshot capture (can be called from outside)
 */
export async function saveMapScreenshot() {
  return await autoSaveMapScreenshot();
}

/**
 * Find and focus on a specific volunteer on the map
 * @param {string} volunteerCode - The code of the volunteer to find
 */
export function findVolunteerOnMap(volunteerCode) {
  if (!map) return;
  const marker = markers[volunteerCode];
  if (marker) {
    map.setView(marker.getLatLng(), 1); // Zoom in
    marker.openPopup();

    // Visual feedback (temporary pulse)
    const originalStyle = { radius: marker.options.radius };
    marker.setRadius(20);
    setTimeout(() => marker.setRadius(originalStyle.radius), 1000);
  } else {
    console.warn(`Volunteer marker not found for code: ${volunteerCode}`);
  }
}
