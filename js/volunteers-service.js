/**
 * Volunteers Service
 * CRUD operations for volunteers table with Supabase
 */
import { supabase } from "./supabase-client.js";

/**
 * Get all volunteers
 * @returns {Promise<Array>}
 */
export async function getAllVolunteers() {
  const { data, error } = await supabase
    .from("volunteers")
    .select("*")
    .order("name");

  if (error) {
    console.error("Error fetching volunteers:", error);
    return [];
  }
  return data;
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
