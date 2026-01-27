-- Supabase SQL: Add callback columns to volunteers table
-- Run this in your Supabase SQL editor

ALTER TABLE volunteers 
ADD COLUMN IF NOT EXISTS callback_comment TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS callback_comment_date TIMESTAMP WITH TIME ZONE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS callback_comment_approval TEXT DEFAULT NULL;

-- Optional: Add a check constraint for approval status
ALTER TABLE volunteers 
ADD CONSTRAINT callback_approval_check 
CHECK (callback_comment_approval IS NULL OR callback_comment_approval IN ('pending', 'approved', 'rejected'));
