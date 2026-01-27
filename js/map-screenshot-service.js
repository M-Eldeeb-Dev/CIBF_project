/**
 * Map Screenshot Service
 * Captures Leaflet map as image and uploads to Supabase Storage
 */
import { supabase } from "./supabase-client.js";

/**
 * Capture the current map view as an image using html2canvas
 * @param {HTMLElement} mapContainer - The map container element
 * @returns {Promise<Blob>} - The image blob
 */
export async function captureMapScreenshot(mapContainer) {
  // Dynamically load html2canvas if not already loaded
  if (!window.html2canvas) {
    await loadHtml2Canvas();
  }

  return new Promise((resolve, reject) => {
    html2canvas(mapContainer, {
      useCORS: true,
      allowTaint: true,
      backgroundColor: "#ffffff",
      scale: 1,
      logging: false,
    })
      .then((canvas) => {
        canvas.toBlob(
          (blob) => {
            if (blob) {
              resolve(blob);
            } else {
              reject(new Error("Failed to create blob from canvas"));
            }
          },
          "image/png",
          0.9,
        );
      })
      .catch(reject);
  });
}

/**
 * Load html2canvas library dynamically
 */
async function loadHtml2Canvas() {
  return new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src =
      "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js";
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
}

/**
 * Upload map screenshot to Supabase Storage
 * @param {Blob} imageBlob - The image blob
 * @param {number} hallId - Hall ID (1-5)
 * @returns {Promise<string|null>} - Public URL of uploaded image or null on error
 */
export async function uploadMapScreenshot(imageBlob, hallId) {
  const fileName = `hall_${hallId}_map_${Date.now()}.png`;
  const filePath = `map_screenshots/${fileName}`;

  try {
    // Upload to Supabase Storage
    const { data, error } = await supabase.storage
      .from("maps")
      .upload(filePath, imageBlob, {
        contentType: "image/png",
        upsert: true,
      });

    if (error) {
      console.error("Error uploading screenshot:", error);
      return null;
    }

    // Get public URL
    const { data: urlData } = supabase.storage
      .from("maps")
      .getPublicUrl(filePath);

    return urlData?.publicUrl || null;
  } catch (error) {
    console.error("Error in uploadMapScreenshot:", error);
    return null;
  }
}

/**
 * Save map image URL to database
 * @param {number} hallId - Hall ID
 * @param {string} imageUrl - Public URL of the image
 * @returns {Promise<boolean>}
 */
export async function saveMapImage(hallId, imageUrl) {
  try {
    // Check if hall_maps table exists, if not we'll use a different approach
    const { error } = await supabase.from("hall_maps").upsert(
      {
        hall_id: hallId,
        map_image: imageUrl,
        updated_at: new Date().toISOString(),
      },
      {
        onConflict: "hall_id",
      },
    );

    if (error) {
      // If table doesn't exist, we can create a simple version or skip
      console.error("Error saving map image:", error);
      return false;
    }

    return true;
  } catch (error) {
    console.error("Error in saveMapImage:", error);
    return false;
  }
}

/**
 * Capture and upload map screenshot in one call
 * @param {HTMLElement} mapContainer - The map container element
 * @param {number} hallId - Hall ID
 * @returns {Promise<string|null>} - Public URL or null
 */
export async function captureAndUploadMap(mapContainer, hallId) {
  try {
    console.log(`Capturing map screenshot for Hall ${hallId}...`);

    // Give the map a moment to render fully
    await new Promise((resolve) => setTimeout(resolve, 500));

    // Capture screenshot
    const blob = await captureMapScreenshot(mapContainer);
    console.log("Screenshot captured, uploading...");

    // Upload to Supabase
    const imageUrl = await uploadMapScreenshot(blob, hallId);

    if (imageUrl) {
      console.log("Screenshot uploaded:", imageUrl);
      // Save URL to database
      await saveMapImage(hallId, imageUrl);
    }

    return imageUrl;
  } catch (error) {
    console.error("Error capturing and uploading map:", error);
    return null;
  }
}

/**
 * Alternative: Store the map image data directly in volunteers table
 * Updates the map_image field for a specific hall's volunteers
 * @param {number} hallId - Hall ID
 * @param {string} imageUrl - Image URL
 */
export async function updateHallMapImage(hallId, imageUrl) {
  try {
    // Store the latest map image URL somewhere accessible
    // Option 1: Update a specific admin/system record
    // Option 2: Create a separate hall_maps table
    // Option 3: Store in localStorage for quick access

    localStorage.setItem(
      `hall_${hallId}_map`,
      JSON.stringify({
        url: imageUrl,
        updated: new Date().toISOString(),
      }),
    );

    console.log(`Hall ${hallId} map image updated`);
    return true;
  } catch (error) {
    console.error("Error updating hall map image:", error);
    return false;
  }
}
