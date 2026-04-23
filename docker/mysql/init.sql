CREATE DATABASE IF NOT EXISTS saque_pix;
USE saque_pix;

-- ACCOUNT
CREATE TABLE account (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ACCOUNT_WITHDRAW
CREATE TABLE account_withdraw (
    id CHAR(36) PRIMARY KEY,
    account_id CHAR(36) NOT NULL,
    method VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,

    scheduled BOOLEAN NOT NULL DEFAULT FALSE,
    scheduled_for DATETIME NULL,

    done BOOLEAN NOT NULL DEFAULT FALSE,
    error BOOLEAN NOT NULL DEFAULT FALSE,
    error_reason VARCHAR(255) NULL,

    status VARCHAR(50) NOT NULL,
    processed_at DATETIME NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_account_withdraw_account
        FOREIGN KEY (account_id) REFERENCES account(id)
);

CREATE INDEX idx_withdraw_account_id ON account_withdraw(account_id);
CREATE INDEX idx_withdraw_status ON account_withdraw(status);
CREATE INDEX idx_withdraw_scheduled ON account_withdraw(scheduled, scheduled_for);

-- ACCOUNT_WITHDRAW_PIX
CREATE TABLE account_withdraw_pix (
    account_withdraw_id CHAR(36) PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    pix_key VARCHAR(255) NOT NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_withdraw_pix_withdraw
        FOREIGN KEY (account_withdraw_id) REFERENCES account_withdraw(id)
);

-- CRIANDO CONTA INICIAL
INSERT INTO account (id, name, balance)
VALUES (
    '11111111-1111-1111-1111-111111111111',
    'Conta Inicial',
    10000.00
);