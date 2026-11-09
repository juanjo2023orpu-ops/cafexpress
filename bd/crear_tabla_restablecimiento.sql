CREATE TABLE IF NOT EXISTS restablecimiento_contrasenas (
  id int NOT NULL AUTO_INCREMENT,
  persona_id int NOT NULL,
  token_hash char(64) NOT NULL,
  vence_en datetime NOT NULL,
  creado_en timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY token_hash (token_hash),
  KEY persona_id (persona_id),
  CONSTRAINT restablecimiento_contrasenas_ibfk_1
    FOREIGN KEY (persona_id) REFERENCES personas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
