ALTER TABLE utilisateurs
  ADD COLUMN IF NOT EXISTS activation_token VARCHAR(128) NULL,
  ADD COLUMN IF NOT EXISTS reset_token VARCHAR(128) NULL,
  ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME NULL;

ALTER TABLE plans
  ADD COLUMN IF NOT EXISTS client_decision ENUM('en_attente','approuve','refuse') DEFAULT 'en_attente',
  ADD COLUMN IF NOT EXISTS commentaire_client TEXT NULL,
  ADD COLUMN IF NOT EXISTS date_decision_client DATETIME NULL;

ALTER TABLE rapports
  ADD COLUMN IF NOT EXISTS client_decision ENUM('en_attente','approuve','refuse') DEFAULT 'en_attente',
  ADD COLUMN IF NOT EXISTS commentaire_client TEXT NULL,
  ADD COLUMN IF NOT EXISTS date_decision_client DATETIME NULL;
