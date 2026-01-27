/**
 * Authentication Service
 * Handles volunteer and admin authentication via Supabase
 */
import { supabase } from "./supabase-client.js";

/**
 * Authenticate a volunteer by code
 * @param {string} code - Volunteer code (e.g., O-0083)
 * @returns {Promise<{success: boolean, data?: object, error?: string}>}
 */
export async function authenticateVolunteer(code) {
  // Normalize code format
  const normalizedCode = code.toUpperCase().replace(/^O-\s*/, "O-");

  // Check for admin
  if (normalizedCode === "O-9999") {
    return {
      success: true,
      isAdmin: true,
      data: {
        volunteerCode: "O-9999",
        name: "المسؤول",
        group: "Admin",
        period: "N/A",
        sector: "N/A",
      },
    };
  }

  // Query Supabase for volunteer
  const { data, error } = await supabase
    .from("volunteers")
    .select("*")
    .eq("volunteerCode", normalizedCode)
    .single();

  if (error || !data) {
    return {
      success: false,
      error: "الكود غير موجود في النظام",
    };
  }

  return {
    success: true,
    isAdmin: false,
    data: data,
  };
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
 * Store session in localStorage
 */
export function setSession(userData, isAdmin = false) {
  localStorage.setItem(
    "cipf_session",
    JSON.stringify({
      ...userData,
      isAdmin,
      timestamp: Date.now(),
    }),
  );
}

/**
 * Get current session
 */
export function getSession() {
  const session = localStorage.getItem("cipf_session");
  if (!session) return null;

  const data = JSON.parse(session);
  // Session expires after 24 hours
  if (Date.now() - data.timestamp > 86400000) {
    clearSession();
    return null;
  }
  return data;
}

/**
 * Clear session (logout)
 */
export function clearSession() {
  localStorage.removeItem("cipf_session");
}
