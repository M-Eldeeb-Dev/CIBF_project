/**
 * Supabase Client Configuration
 * Cairo Book Fair 2026 - Volunteer Management System
 */
import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

const SUPABASE_URL = "https://ximlhsxjtzakznrcytpu.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhpbWxoc3hqdHpha3pucmN5dHB1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjkzNzY0NzQsImV4cCI6MjA4NDk1MjQ3NH0.MJaamCrETHMvox44f4Q2lz-ygwGEmf7X7tCN1gZW018"; // Replace with your anon key

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// Test connection
export async function testConnection() {
  const { data, error } = await supabase
    .from("volunteers")
    .select("count", { count: "exact", head: true });
  if (error) {
    console.error("Supabase connection error:", error);
    return false;
  }
  console.log("Supabase connected successfully");
  return true;
}
