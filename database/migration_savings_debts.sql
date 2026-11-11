-- Run this once after importing database/quan_ly_chi_tieu.sql.
-- Adds savings goals and debt tracking without changing existing data.
USE quan_ly_chi_tieu;

CREATE TABLE IF NOT EXISTS savings_goals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  target_amount DECIMAL(15,2) NOT NULL,
  target_date DATE DEFAULT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_savings_goal_owner (id, user_id),
  KEY idx_savings_goals_user (user_id, created_at),
  CONSTRAINT fk_savings_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_savings_goal_target CHECK (target_amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS savings_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  goal_id INT NOT NULL,
  user_id INT NOT NULL,
  entry_type ENUM('deposit','withdrawal') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  entry_date DATE NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_savings_entries_goal (goal_id, entry_date, id),
  CONSTRAINT fk_savings_entries_goal_owner FOREIGN KEY (goal_id, user_id) REFERENCES savings_goals(id, user_id) ON DELETE CASCADE,
  CONSTRAINT chk_savings_entry_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS debts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  direction ENUM('payable','receivable') NOT NULL,
  person_name VARCHAR(120) NOT NULL,
  title VARCHAR(150) NOT NULL,
  principal_amount DECIMAL(15,2) NOT NULL,
  due_date DATE DEFAULT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_debt_owner (id, user_id),
  KEY idx_debts_user_direction (user_id, direction, created_at),
  CONSTRAINT fk_debts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_debt_principal CHECK (principal_amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS debt_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  debt_id INT NOT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  entry_date DATE NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_debt_entries_debt (debt_id, entry_date, id),
  CONSTRAINT fk_debt_entries_owner FOREIGN KEY (debt_id, user_id) REFERENCES debts(id, user_id) ON DELETE CASCADE,
  CONSTRAINT chk_debt_entry_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
