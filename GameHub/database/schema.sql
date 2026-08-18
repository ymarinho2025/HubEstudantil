CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    roles INT DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS user_logins (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    ip VARCHAR(45),
    login_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS games (
    id SERIAL PRIMARY KEY,
    slug VARCHAR(60) UNIQUE NOT NULL,
    title VARCHAR(120) NOT NULL,
    description TEXT,
    cover_url TEXT,
    game_url TEXT NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);


UPDATE games SET active=FALSE WHERE slug IN ('flappy-bird','super-mario');

INSERT INTO games(slug,title,description,cover_url,game_url,active) VALUES
('joao-bird','João Bird','Matemática, cálculo mental, atenção e tomada de decisão.','/games/JoaoBird/bg.png','/play.php?game=joao-bird',TRUE),
('datilografia','Corrida de Datilografia','Língua Portuguesa, ortografia e fluência na escrita.',NULL,'/play.php?game=datilografia',TRUE),
('paint','Paint Criativo','Arte, criatividade, composição e teoria das cores.',NULL,'/play.php?game=paint',TRUE),
('piano','Piano Musical','Música, percepção sonora, ritmo e sequência musical.',NULL,'/play.php?game=piano',TRUE),
('pixel-art','Pixel Art','Arte e Matemática, geometria, padrões e criatividade.',NULL,'/play.php?game=pixel-art',TRUE),
('snake','Snake Lógico','Raciocínio lógico, planejamento espacial e estratégia.',NULL,'/play.php?game=snake',TRUE),
('space-invaders','Space Invaders Ciência','Ciências, astronomia básica, atenção e coordenação.',NULL,'/play.php?game=space-invaders',TRUE)
ON CONFLICT (slug) DO UPDATE SET
title=EXCLUDED.title,description=EXCLUDED.description,cover_url=EXCLUDED.cover_url,
game_url=EXCLUDED.game_url,active=EXCLUDED.active;

CREATE TABLE IF NOT EXISTS game_scores (
    id BIGSERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    game_id INT NOT NULL REFERENCES games(id) ON DELETE CASCADE,
    best_score INT NOT NULL DEFAULT 0 CHECK (best_score >= 0),
    last_score INT NOT NULL DEFAULT 0 CHECK (last_score >= 0),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE(user_id, game_id)
);

CREATE TABLE IF NOT EXISTS game_score_history (
    id BIGSERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    game_id INT NOT NULL REFERENCES games(id) ON DELETE CASCADE,
    score INT NOT NULL CHECK (score >= 0),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_game_scores_ranking ON game_scores(game_id, best_score DESC, updated_at ASC);
CREATE INDEX IF NOT EXISTS idx_score_history_user_game ON game_score_history(user_id, game_id, created_at DESC);

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_game_scores_updated_at ON game_scores;
CREATE TRIGGER trg_game_scores_updated_at
BEFORE UPDATE ON game_scores
FOR EACH ROW
EXECUTE FUNCTION set_updated_at();


CREATE TABLE IF NOT EXISTS qi_points (
    user_id INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    points INT NOT NULL DEFAULT 0 CHECK (points >= 0),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS qi_history (
    id BIGSERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    game_slug VARCHAR(60) NOT NULL,
    points INT NOT NULL DEFAULT 10 CHECK (points > 0),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_qi_history_user ON qi_history(user_id, created_at DESC);
