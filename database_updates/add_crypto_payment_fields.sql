-- Add crypto payment fields to the payments table
-- Run this SQL script on your database to add crypto payment support

ALTER TABLE payments 
ADD COLUMN payment_method VARCHAR(50) NULL AFTER datetime_expire,
ADD COLUMN crypto_invoice_id VARCHAR(255) NULL AFTER payment_method,
ADD COLUMN crypto_currency VARCHAR(10) NULL AFTER crypto_invoice_id;

-- Add indexes for better performance
CREATE INDEX idx_payments_payment_method ON payments(payment_method);
CREATE INDEX idx_payments_crypto_invoice_id ON payments(crypto_invoice_id);

-- Update existing payments to have 'card' as payment method
UPDATE payments SET payment_method = 'card' WHERE payment_method IS NULL; 