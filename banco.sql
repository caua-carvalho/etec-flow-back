-- --------------------------------------------------
-- Script ajustado de criação de tabelas
-- Gerenciamento de grade de aulas
-- --------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS grade_aulas;
DROP TABLE IF EXISTS salas;
DROP TABLE IF EXISTS disciplinas;
DROP TABLE IF EXISTS professores;
DROP TABLE IF EXISTS turmas;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS escolas;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Unidades de Ensino
CREATE TABLE escolas (
  id_escola       INT           PRIMARY KEY AUTO_INCREMENT,
  nome            VARCHAR(255)  NOT NULL
);

-- 2. Cursos por Escola
CREATE TABLE cursos (
  id_curso        INT           PRIMARY KEY AUTO_INCREMENT,
  id_escola       INT           NOT NULL,
  nome            VARCHAR(255)  NOT NULL,
  CONSTRAINT fk_cursos_escolas
    FOREIGN KEY (id_escola) REFERENCES escolas(id_escola)
);

-- 3. Turmas (grupos de alunos)
CREATE TABLE turmas (
  id_turma        INT            PRIMARY KEY AUTO_INCREMENT,
  id_curso        INT            NOT NULL,
  codigo          VARCHAR(50)    NOT NULL,      -- reintroduzido para identificação
  divisao         ENUM('A','B')  NOT NULL,
  CONSTRAINT fk_turmas_cursos
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso),
  UNIQUE(id_curso, codigo)                      -- agora consistente
);

-- 4. Professores
CREATE TABLE professores (
  id_professor    INT           PRIMARY KEY AUTO_INCREMENT,
  nome            VARCHAR(255)  NOT NULL,
  email           VARCHAR(255)  UNIQUE,
  telefone        VARCHAR(20)
);

-- 5. Disciplinas
CREATE TABLE disciplinas (
  id_disciplina   INT           PRIMARY KEY AUTO_INCREMENT,
  id_curso        INT           NOT NULL,
  nome            VARCHAR(255)  NOT NULL,
  CONSTRAINT fk_disciplinas_cursos
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso),
  UNIQUE(id_curso, nome)
);

-- 7. Grade de Aulas
CREATE TABLE grade_aulas (
  id_grade        INT           PRIMARY KEY AUTO_INCREMENT,
  id_turma        INT           NOT NULL,
  id_disciplina   INT           NOT NULL,
  id_professor    INT           NOT NULL,
  sala            VARCHAR(30)   NOT NULL,       -- volta a ser INT
  dia_semana      TINYINT       NOT NULL,       -- 0=Dom…6=Sáb
  horario_inicio  TIME          NOT NULL,
  horario_fim     TIME          NOT NULL,
  cor_evento      CHAR(7)       DEFAULT '#CCCCCC',
  CONSTRAINT fk_grade_turmas
    FOREIGN KEY (id_turma) REFERENCES turmas(id_turma),
  CONSTRAINT fk_grade_disciplinas
    FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina),
  CONSTRAINT fk_grade_professores
    FOREIGN KEY (id_professor) REFERENCES professores(id_professor),
  UNIQUE(id_turma, dia_semana, horario_inicio)
);

-- Índices para performance
CREATE INDEX idx_grade_professor_dia
  ON grade_aulas (id_professor, dia_semana);
CREATE INDEX idx_grade_turma_dia
  ON grade_aulas (id_turma, dia_semana);
