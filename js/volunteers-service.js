/**
 * Volunteers Service
 * CRUD operations for volunteers table with Supabase
 */
import { supabase } from "./supabase-client.js";

// Cache for volunteers data
let volunteersCache = null;
let cacheTimestamp = 0;
const CACHE_DURATION = 30000; // 30 seconds cache

// Debounce helper
const debounceMap = new Map();
function debounce(key, fn, delay = 300) {
  if (debounceMap.has(key)) {
    clearTimeout(debounceMap.get(key));
  }
  return new Promise((resolve) => {
    const timeout = setTimeout(async () => {
      debounceMap.delete(key);
      resolve(await fn());
    }, delay);
    debounceMap.set(key, timeout);
  });
}

/**
 * Get all volunteers with caching
 * @param {boolean} forceRefresh - Force refresh cache
 * @returns {Promise<Array>}
 */
export async function getAllVolunteers(forceRefresh = false) {
  const now = Date.now();

  // Return cached data if valid and not forcing refresh
  if (
    !forceRefresh &&
    volunteersCache &&
    now - cacheTimestamp < CACHE_DURATION
  ) {
    return volunteersCache;
  }

  const { data, error } = await supabase
    .from("volunteers")
    .select("*")
    .order("name");

  if (error) {
    console.error("Error fetching volunteers:", error);
    return volunteersCache || []; // Return stale cache on error
  }

  // Update cache
  volunteersCache = data;
  cacheTimestamp = now;

  return data;
}

/**
 * Invalidate volunteers cache (call after mutations)
 */
export function invalidateVolunteersCache() {
  volunteersCache = null;
  cacheTimestamp = 0;
}

/**
 * Get volunteers by hall
 * @param {number} hallId - Hall ID (1-5)
 * @returns {Promise<Array>}
 */
export async function getVolunteersByHall(hallId) {
  const { data, error } = await supabase
    .from("volunteers")
    .select("*")
    .eq("hall_id", hallId)
    .order("name");

  if (error) {
    console.error("Error fetching volunteers by hall:", error);
    return [];
  }
  return data;
}

/**
 * Get available volunteers (not occupied, optionally by hall)
 * @param {number|null} hallId - Optional hall filter
 * @returns {Promise<Array>}
 */
export async function getAvailableVolunteers(hallId = null) {
  let query = supabase
    .from("volunteers")
    .select("*")
    .or("is_present.eq.false,is_occupied.eq.false");

  if (hallId) {
    query = query.eq("hall_id", hallId);
  }

  const { data, error } = await query.order("name");

  if (error) {
    console.error("Error fetching available volunteers:", error);
    return [];
  }
  return data;
}

/**
 * Get occupied volunteers (assigned to spots)
 * @param {number|null} hallId - Optional hall filter
 * @returns {Promise<Array>}
 */
export async function getOccupiedVolunteers(hallId = null) {
  let query = supabase
    .from("volunteers")
    .select("*")
    .or("is_present.eq.true,is_occupied.eq.true");

  if (hallId) {
    query = query.eq("hall_id", hallId);
  }

  const { data, error } = await query.order("name");

  if (error) {
    console.error("Error fetching occupied volunteers:", error);
    return [];
  }
  return data;
}

/**
 * Assign volunteer to a spot (mark as present)
 * @param {string} volunteerCode - Volunteer code
 * @param {number} hallId - Hall ID
 * @param {string} location - Current location description
 * @returns {Promise<boolean>}
 */
export async function assignVolunteer(volunteerCode, hallId, location = "") {
  const { error } = await supabase
    .from("volunteers")
    .update({
      is_present: true,
      is_occupied: true,
      hall_id: hallId,
      current_loc: location,
      reason: null,
      reasons_date: null,
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error assigning volunteer:", error);
    return false;
  }
  return true;
}

/**
 * Remove volunteer from spot (mark as not present)
 * @param {string} volunteerCode - Volunteer code
 * @returns {Promise<boolean>}
 */
export async function removeVolunteer(volunteerCode) {
  const { error } = await supabase
    .from("volunteers")
    .update({
      is_present: false,
      is_occupied: false,
      current_loc: null,
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error removing volunteer:", error);
    return false;
  }
  return true;
}

/**
 * Subscribe to real-time volunteer changes
 * @param {function} callback - Callback function for changes
 * @returns {object} - Subscription object
 */
export function subscribeToVolunteers(callback) {
  return supabase
    .channel("public:volunteers")
    .on(
      "postgres_changes",
      { event: "*", schema: "public", table: "volunteers" },
      (payload) => {
        console.log("Realtime change:", payload);
        callback(payload);
      },
    )
    .subscribe();
}

/**
 * Unsubscribe from real-time changes
 * @param {object} subscription - Subscription object
 */
export function unsubscribeFromVolunteers(subscription) {
  if (subscription) {
    supabase.removeChannel(subscription);
  }
}

/**
 * Get volunteer by code
 * @param {string} code - Volunteer code
 * @returns {Promise<object|null>}
 */
export async function getVolunteerByCode(code) {
  const { data, error } = await supabase
    .from("volunteers")
    .select("*")
    .eq("volunteerCode", code)
    .single();

  if (error) {
    console.error("Error fetching volunteer:", error);
    return null;
  }
  return data;
}

/**
 * Submit a callback request (volunteer wants to return)
 * @param {string} volunteerCode - Volunteer code
 * @param {string} comment - Message to admin
 * @returns {Promise<boolean>}
 */
export async function submitCallbackRequest(volunteerCode, comment) {
  const { error } = await supabase
    .from("volunteers")
    .update({
      callback_comment: comment,
      callback_comment_date: new Date().toISOString(),
      callback_comment_approval: "pending",
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error submitting callback request:", error);
    return false;
  }
  return true;
}

/**
 * Get all pending callback requests (for admin)
 * @returns {Promise<Array>}
 */
export async function getCallbackRequests() {
  const { data, error } = await supabase
    .from("volunteers")
    .select("*")
    .eq("callback_comment_approval", "pending")
    .order("callback_comment_date", { ascending: false });

  if (error) {
    console.error("Error fetching callback requests:", error);
    return [];
  }
  return data;
}

/**
 * Approve a callback request (admin action)
 * Clears the callback comment after approval
 * @param {string} volunteerCode - Volunteer code
 * @returns {Promise<boolean>}
 */
export async function approveCallbackRequest(volunteerCode) {
  const { error } = await supabase
    .from("volunteers")
    .update({
      callback_comment_approval: "approved",
      callback_comment: null, // Clear the message after approval
      callback_comment_date: null,
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error approving callback request:", error);
    return false;
  }

  // Invalidate cache
  invalidateVolunteersCache();
  return true;
}

/**
 * Reject a callback request (admin action)
 * @param {string} volunteerCode - Volunteer code
 * @returns {Promise<boolean>}
 */
export async function rejectCallbackRequest(volunteerCode) {
  const { error } = await supabase
    .from("volunteers")
    .update({
      callback_comment_approval: "rejected",
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error rejecting callback request:", error);
    return false;
  }

  // Invalidate cache
  invalidateVolunteersCache();
  return true;
}

/**
 * Clear callback request (reset after action)
 * @param {string} volunteerCode - Volunteer code
 * @returns {Promise<boolean>}
 */
export async function clearCallbackRequest(volunteerCode) {
  const { error } = await supabase
    .from("volunteers")
    .update({
      callback_comment: null,
      callback_comment_date: null,
      callback_comment_approval: null,
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error clearing callback request:", error);
    return false;
  }

  // Invalidate cache
  invalidateVolunteersCache();
  return true;
}

/**
 * Delete own callback request (volunteer action)
 * Allows volunteers to cancel their pending request
 * @param {string} volunteerCode - Volunteer code
 * @returns {Promise<boolean>}
 */
export async function deleteCallbackRequest(volunteerCode) {
  // Only delete if it's still pending (volunteer can only delete pending requests)
  const { error } = await supabase
    .from("volunteers")
    .update({
      callback_comment: null,
      callback_comment_date: null,
      callback_comment_approval: null,
    })
    .eq("volunteerCode", volunteerCode)
    .eq("callback_comment_approval", "pending");

  if (error) {
    console.error("Error deleting callback request:", error);
    return false;
  }

  // Invalidate cache
  invalidateVolunteersCache();
  return true;
}

/**
 * Clear removal reason (delete from log)
 * @param {string} volunteerCode - Volunteer code
 * @returns {Promise<boolean>}
 */
export async function clearDeleteReason(volunteerCode) {
  const { error } = await supabase
    .from("volunteers")
    .update({
      reason: null,
      reasons_date: null,
    })
    .eq("volunteerCode", volunteerCode);

  if (error) {
    console.error("Error clearing removal reason:", error);
    return false;
  }

  // Invalidate cache
  invalidateVolunteersCache();
  return true;
}
