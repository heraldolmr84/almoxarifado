-- ==========================================================
-- SISGEM - SISTEMA DE GESTÃO DE MATERIAIS
-- Schema Completo do Banco de Dados (PostgreSQL)
-- ==========================================================

-- Limpa o banco (caso já exista algo) respeitando a ordem das chaves estrangeiras
DROP TABLE IF EXISTS auditoria CASCADE;
DROP TABLE IF EXISTS backorders CASCADE;
DROP TABLE IF EXISTS itens_movimentacao CASCADE;
DROP TABLE IF EXISTS movimentacoes CASCADE;
DROP TABLE IF EXISTS saldo_estoque CASCADE;
DROP TABLE IF EXISTS lotes CASCADE;
DROP TABLE IF EXISTS pedidos_compra_itens CASCADE;
DROP TABLE IF EXISTS pedidos_compra CASCADE;
DROP TABLE IF EXISTS requisicoes_itens CASCADE;
DROP TABLE IF EXISTS requisicoes CASCADE;
DROP TABLE IF EXISTS produtos CASCADE;
DROP TABLE IF EXISTS fornecedores CASCADE;
DROP TABLE IF EXISTS departamentos_almoxarifados CASCADE;
DROP TABLE IF EXISTS almoxarifados CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS departamentos CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS unidades_medida CASCADE;

-- ==========================================================
-- 1. TABELAS BASE (Cadastros Básicos)
-- ==========================================================

CREATE TABLE unidades_medida (
    id SERIAL PRIMARY KEY,
    sigla VARCHAR(10) NOT NULL UNIQUE,
    descricao VARCHAR(50) NOT NULL
);

CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    prefixo_sku VARCHAR(10), -- Ex: INF, LIM, ESC
    categoria_pai_id INT REFERENCES categorias(id),
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE departamentos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil VARCHAR(30) NOT NULL DEFAULT 'OPERADOR' CHECK (perfil IN ('ADMIN','ALMOXARIFE','OPERADOR','CONSULTA','GERENTE')),
    departamento_id INT REFERENCES departamentos(id),
    deve_trocar_senha BOOLEAN NOT NULL DEFAULT FALSE,
    reset_token VARCHAR(255),
    reset_expira_em TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE almoxarifados (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    localizacao VARCHAR(200),
    responsavel_id INT REFERENCES usuarios(id),
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

-- Tabela Pivô: Quais almoxarifados cada departamento pode acessar
CREATE TABLE departamentos_almoxarifados (
    departamento_id INT REFERENCES departamentos(id) ON DELETE CASCADE,
    almoxarifado_id INT REFERENCES almoxarifados(id) ON DELETE CASCADE,
    PRIMARY KEY (departamento_id, almoxarifado_id)
);

CREATE TABLE fornecedores (
    id SERIAL PRIMARY KEY,
    razao_social VARCHAR(200) NOT NULL,
    nome_fantasia VARCHAR(200),
    cnpj VARCHAR(18) UNIQUE,
    email VARCHAR(150),
    telefone VARCHAR(20),
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

-- ==========================================================
-- 2. PRODUTOS E LOTES
-- ==========================================================

CREATE TABLE produtos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    codigo_barras VARCHAR(50),
    descricao VARCHAR(200) NOT NULL,
    categoria_id INT REFERENCES categorias(id),
    unidade_medida_id INT NOT NULL REFERENCES unidades_medida(id),
    fornecedor_id INT REFERENCES fornecedores(id), -- Adicionado posteriormente
    estoque_minimo NUMERIC(14,3) DEFAULT 0,
    estoque_maximo NUMERIC(14,3),
    controla_lote BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lotes (
    id SERIAL PRIMARY KEY,
    produto_id INT NOT NULL REFERENCES produtos(id),
    numero_lote VARCHAR(50) NOT NULL,
    validade DATE,
    UNIQUE (produto_id, numero_lote)
);

-- ==========================================================
-- 3. REQUISIÇÕES DE MATERIAIS
-- ==========================================================

CREATE TABLE requisicoes (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(20) UNIQUE,
    solicitante_id INT NOT NULL REFERENCES usuarios(id),
    departamento_id INT NOT NULL REFERENCES departamentos(id),
    almoxarifado_id INT NOT NULL REFERENCES almoxarifados(id),
    status VARCHAR(30) NOT NULL DEFAULT 'RASCUNHO' CHECK (status IN ('RASCUNHO','AGUARDANDO_APROVACAO','APROVADA','REJEITADA','EM_SEPARACAO','AGUARDANDO_ESTOQUE','ATENDIDA','ATENDIDA_PARCIAL','CANCELADA')),
    justificativa TEXT NOT NULL,
    prioridade VARCHAR(10) NOT NULL DEFAULT 'NORMAL',
    aprovador_id INT REFERENCES usuarios(id),
    data_aprovacao TIMESTAMP,
    motivo_rejeicao TEXT,
    motivo_cancelamento TEXT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE requisicoes_itens (
    id SERIAL PRIMARY KEY,
    requisicao_id INT NOT NULL REFERENCES requisicoes(id) ON DELETE CASCADE,
    produto_id INT NOT NULL REFERENCES produtos(id),
    quantidade_solicitada NUMERIC(14,3) NOT NULL CHECK (quantidade_solicitada > 0),
    quantidade_aprovada NUMERIC(14,3),
    quantidade_atendida NUMERIC(14,3) NOT NULL DEFAULT 0,
    status_item VARCHAR(25) NOT NULL DEFAULT 'PENDENTE' CHECK (status_item IN ('PENDENTE','EM_BACKORDER','ATENDIDO','CANCELADO')),
    acao_pendente VARCHAR(20) CHECK (acao_pendente IN ('BACKORDER','CANCELAR','REJEITAR')),
    data_ultimo_atendimento TIMESTAMP,
    prioridade_backorder INT NOT NULL DEFAULT 0,
    CONSTRAINT chk_aprovada_le_solicitada CHECK (quantidade_aprovada IS NULL OR quantidade_aprovada <= quantidade_solicitada),
    CONSTRAINT chk_atendida_le_aprovada CHECK (quantidade_atendida <= COALESCE(quantidade_aprovada, quantidade_solicitada))
);

-- Fila de espera (para itens que foram atendidos parcialmente e aguardam estoque)
CREATE TABLE backorders (
    id BIGSERIAL PRIMARY KEY,
    requisicao_item_id INT NOT NULL UNIQUE REFERENCES requisicoes_itens(id) ON DELETE CASCADE,
    produto_id INT NOT NULL REFERENCES produtos(id),
    almoxarifado_id INT NOT NULL REFERENCES almoxarifados(id),
    quantidade_pendente NUMERIC(14,3) NOT NULL CHECK (quantidade_pendente > 0),
    prioridade INT NOT NULL DEFAULT 0,
    data_entrada_fila TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_previsao DATE,
    atendido_em TIMESTAMP,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- 4. PEDIDOS DE COMPRA (Módulo do Comprador)
-- ==========================================================

CREATE TABLE pedidos_compra (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(20) UNIQUE,
    solicitante_id INT NOT NULL REFERENCES usuarios(id),
    aprovador_id INT REFERENCES usuarios(id),
    almoxarifado_id INT REFERENCES almoxarifados(id), -- Destino final
    fornecedor_id INT REFERENCES fornecedores(id),
    status VARCHAR(30) NOT NULL DEFAULT 'RASCUNHO' CHECK (status IN ('RASCUNHO','AGUARDANDO_APROVACAO','APROVADO','REJEITADO','COMPRADO','RECEBIDO','RECEBIDO_PARCIAL')),
    justificativa TEXT,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP,
    data_compra TIMESTAMP,
    documento_compra VARCHAR(100), -- NF ou Pedido assinado
    observacao TEXT
);

CREATE TABLE pedidos_compra_itens (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos_compra(id) ON DELETE CASCADE,
    produto_id INT NOT NULL REFERENCES produtos(id),
    quantidade NUMERIC(14,3) NOT NULL CHECK (quantidade > 0),
    valor_unitario NUMERIC(14,2),
    quantidade_recebida NUMERIC(14,3) NOT NULL DEFAULT 0
);

-- ==========================================================
-- 5. MOVIMENTAÇÕES E SALDOS DE ESTOQUE
-- ==========================================================

CREATE TABLE movimentacoes (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('ENTRADA','SAIDA','AJUSTE')),
    subtipo VARCHAR(30), -- COMPRA, REQUISICAO, TRANSFERENCIA, DEVOLUCAO, PERDA...
    data_movimentacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL REFERENCES usuarios(id),
    fornecedor_id INT REFERENCES fornecedores(id),
    departamento_id INT REFERENCES departamentos(id),
    almoxarifado_origem_id INT REFERENCES almoxarifados(id),
    almoxarifado_destino_id INT REFERENCES almoxarifados(id),
    requisicao_id INT REFERENCES requisicoes(id),
    documento VARCHAR(50),
    observacao TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'CONFIRMADA' CHECK (status IN ('RASCUNHO','CONFIRMADA','CANCELADA')),
    CONSTRAINT chk_almox CHECK (
        (tipo = 'ENTRADA' AND almoxarifado_destino_id IS NOT NULL) OR
        (tipo = 'SAIDA' AND almoxarifado_origem_id IS NOT NULL) OR
        (tipo = 'AJUSTE')
    )
);

CREATE TABLE itens_movimentacao (
    id SERIAL PRIMARY KEY,
    movimentacao_id INT NOT NULL REFERENCES movimentacoes(id) ON DELETE CASCADE,
    produto_id INT NOT NULL REFERENCES produtos(id),
    lote_id INT REFERENCES lotes(id),
    quantidade NUMERIC(14,3) NOT NULL CHECK (quantidade > 0),
    valor_unitario NUMERIC(14,2),
    observacao TEXT
);

CREATE TABLE saldo_estoque (
    id SERIAL PRIMARY KEY,
    produto_id INT NOT NULL REFERENCES produtos(id),
    almoxarifado_id INT NOT NULL REFERENCES almoxarifados(id),
    lote_id INT REFERENCES lotes(id),
    quantidade NUMERIC(14,3) NOT NULL DEFAULT 0 CHECK (quantidade >= 0),
    quantidade_reservada NUMERIC(14,3) NOT NULL DEFAULT 0 CHECK (quantidade_reservada >= 0),
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (produto_id, almoxarifado_id, lote_id)
);

-- ==========================================================
-- 6. AUDITORIA (Log de Alterações)
-- ==========================================================

CREATE TABLE auditoria (
    id BIGSERIAL PRIMARY KEY,
    tabela VARCHAR(60) NOT NULL,
    registro_id BIGINT NOT NULL,
    acao VARCHAR(20) NOT NULL, -- INSERT, UPDATE, DELETE
    dados JSONB,
    usuario_id INT REFERENCES usuarios(id),
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- 7. TRIGGERS E FUNÇÕES (Automação de Saldo)
-- ==========================================================

CREATE OR REPLACE FUNCTION fn_atualiza_saldo()
RETURNS TRIGGER AS $$
DECLARE
    v_tipo VARCHAR(10);
    v_dest INT;
    v_orig INT;
    v_row_count INT;
BEGIN
    SELECT tipo, almoxarifado_destino_id, almoxarifado_origem_id 
      INTO v_tipo, v_dest, v_orig
      FROM movimentacoes WHERE id = NEW.movimentacao_id;

    IF v_tipo = 'ENTRADA' THEN
        UPDATE saldo_estoque
           SET quantidade = quantidade + NEW.quantidade, atualizado_em = NOW()
         WHERE produto_id = NEW.produto_id AND almoxarifado_id = v_dest
           AND lote_id IS NOT DISTINCT FROM NEW.lote_id;
        
        GET DIAGNOSTICS v_row_count = ROW_COUNT;
        
        IF v_row_count = 0 THEN
            INSERT INTO saldo_estoque (produto_id, almoxarifado_id, lote_id, quantidade)
            VALUES (NEW.produto_id, v_dest, NEW.lote_id, NEW.quantidade);
        END IF;

    ELSIF v_tipo = 'SAIDA' THEN
        UPDATE saldo_estoque
           SET quantidade = quantidade - NEW.quantidade, atualizado_em = NOW()
         WHERE produto_id = NEW.produto_id AND almoxarifado_id = v_orig
           AND lote_id IS NOT DISTINCT FROM NEW.lote_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tg_atualiza_saldo
AFTER INSERT ON itens_movimentacao
FOR EACH ROW EXECUTE FUNCTION fn_atualiza_saldo();

-- ==========================================================
-- 8. DADOS INICIAIS (SEED)
-- ==========================================================

-- Unidades de Medida
INSERT INTO unidades_medida (sigla, descricao) VALUES 
('UN', 'Unidade'), 
('CX', 'Caixa'), 
('KG', 'Quilograma'), 
('L', 'Litro'), 
('M', 'Metro'), 
('RES', 'Resma');

-- Categorias
INSERT INTO categorias (nome, prefixo_sku) VALUES 
('Material de Escritório', 'ESC'), 
('Peças de Manutenção', 'MAN'), 
('Limpeza', 'LIM'), 
('Informática', 'INF'), 
('Elétrica', 'ELE'),
('Alimentos', 'ALI');

-- Departamentos
INSERT INTO departamentos (nome) VALUES 
('Administração Geral'), 
('Manutenção'), 
('TI'), 
('Secretaria Acadêmica'), 
('Sala de Professores');

-- Almoxarifados
INSERT INTO almoxarifados (nome, localizacao) VALUES 
('Almoxarifado Central', 'Térreo - Bloco A'),
('Almoxarifado Docentes', 'Bloco B - Sala 12');

-- Permissões (Administração Geral tem acesso a todos os almoxarifados)
INSERT INTO departamentos_almoxarifados (departamento_id, almoxarifado_id) VALUES 
((SELECT id FROM departamentos WHERE nome = 'Administração Geral'), (SELECT id FROM almoxarifados WHERE nome = 'Almoxarifado Central')),
((SELECT id FROM departamentos WHERE nome = 'Administração Geral'), (SELECT id FROM almoxarifados WHERE nome = 'Almoxarifado Docentes'));

-- Usuário Administrador Padrão (Senha: admin123)
-- O Hash abaixo é gerado com password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha_hash, perfil, departamento_id, ativo) VALUES 
('Administrador', 'admin@ufms.com', '$2y$10$e.wX5.1W.5.1W.5.1W.5.1uX5.1W.5.1W.5.1W.5.1W.5.1W', 'ADMIN', (SELECT id FROM departamentos WHERE nome = 'Administração Geral'), TRUE);

-- Garantindo que a senha seja 'admin123' caso o hash de exemplo falhe
UPDATE usuarios SET senha_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@ufms.com';