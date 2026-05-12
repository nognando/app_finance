<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';

try {
    $db = getDB();

    switch ($action) {

        // ─── DASHBOARD ───────────────────────────────────────
        case 'dashboard':
            $mes  = intval($_GET['mes']  ?? date('n'));
            $ano  = intval($_GET['ano']  ?? date('Y'));
            $mesStr = sprintf('%04d-%02d', $ano, $mes);

            // Totais do mês
            $stmt = $db->prepare("
                SELECT
                    SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END) as total_receitas,
                    SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END) as total_despesas,
                    SUM(CASE WHEN tipo='despesa' AND status='pendente' THEN valor ELSE 0 END) as total_pendente,
                    SUM(CASE WHEN tipo='despesa' AND status='pago' THEN valor ELSE 0 END) as total_pago
                FROM transacoes
                WHERE DATE_FORMAT(data_vencimento, '%Y-%m') = ?
                  AND status != 'cancelado'
            ");
            $stmt->execute([$mesStr]);
            $totais = $stmt->fetch();

            // Despesas por categoria
            $stmt = $db->prepare("
                SELECT c.nome, c.cor, SUM(t.valor) as total
                FROM transacoes t
                LEFT JOIN categorias c ON t.categoria_id = c.id
                WHERE DATE_FORMAT(t.data_vencimento, '%Y-%m') = ?
                  AND t.tipo = 'despesa'
                  AND t.status != 'cancelado'
                GROUP BY c.id, c.nome, c.cor
                ORDER BY total DESC
                LIMIT 8
            ");
            $stmt->execute([$mesStr]);
            $categorias = $stmt->fetchAll();

            // Pendentes do mês (para dashboard)
            $stmt = $db->prepare("
                SELECT t.*, c.nome as categoria_nome, c.cor as categoria_cor,
                       DATEDIFF(t.data_vencimento, CURDATE()) as dias_para_vencer
                FROM transacoes t
                LEFT JOIN categorias c ON t.categoria_id = c.id
                WHERE DATE_FORMAT(t.data_vencimento, '%Y-%m') = ?
                  AND t.status = 'pendente'
                ORDER BY t.data_vencimento ASC
                LIMIT 15
            ");
            $stmt->execute([$mesStr]);
            $ultimas = $stmt->fetchAll();

            // Pendentes com vencimento próximo (próximos 7 dias)
            $stmt = $db->prepare("
                SELECT t.*, c.nome as categoria_nome, c.cor as categoria_cor
                FROM transacoes t
                LEFT JOIN categorias c ON t.categoria_id = c.id
                WHERE t.status = 'pendente'
                  AND t.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY t.data_vencimento ASC
                LIMIT 5
            ");
            $stmt->execute();
            $vencendo = $stmt->fetchAll();

            jsonResponse([
                'totais'     => $totais,
                'categorias' => $categorias,
                'ultimas'    => $ultimas,
                'vencendo'   => $vencendo,
            ]);

        // ─── LISTAR TRANSAÇÕES ────────────────────────────────
        case 'listar':
            $mes        = intval($_GET['mes']        ?? date('n'));
            $ano        = intval($_GET['ano']        ?? date('Y'));
            $tipo       = $_GET['tipo']              ?? '';
            $status     = $_GET['status']            ?? '';
            $categoria  = intval($_GET['categoria']  ?? 0);
            $busca      = trim($_GET['busca']        ?? '');
            $mesStr     = sprintf('%04d-%02d', $ano, $mes);

            $where  = ["DATE_FORMAT(t.data_vencimento, '%Y-%m') = :mes", "t.status != 'cancelado'"];
            $params = [':mes' => $mesStr];

            if ($tipo)      { $where[] = "t.tipo = :tipo";              $params[':tipo']      = $tipo; }
            if ($status)    { $where[] = "t.status = :status";          $params[':status']    = $status; }
            if ($categoria) { $where[] = "t.categoria_id = :categoria"; $params[':categoria'] = $categoria; }
            if ($busca)     { $where[] = "t.descricao LIKE :busca";     $params[':busca']     = "%$busca%"; }

            $sql = "
                SELECT t.*, c.nome as categoria_nome, c.cor as categoria_cor, c.icone as categoria_icone
                FROM transacoes t
                LEFT JOIN categorias c ON t.categoria_id = c.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.data_vencimento ASC, t.id ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            jsonResponse(['transacoes' => $rows]);

        // ─── PENDENTES ───────────────────────────────────────
        case 'pendentes':
            $mes    = intval($_GET['mes'] ?? 0);
            $ano    = intval($_GET['ano'] ?? date('Y'));
            $where  = ["t.status = 'pendente'"];
            $params = [];

            if ($mes) {
                $mesStr = sprintf('%04d-%02d', $ano, $mes);
                $where[] = "DATE_FORMAT(t.data_vencimento, '%Y-%m') = :mes";
                $params[':mes'] = $mesStr;
            }

            $sql = "
                SELECT t.*, c.nome as categoria_nome, c.cor as categoria_cor,
                       DATEDIFF(t.data_vencimento, CURDATE()) as dias_para_vencer
                FROM transacoes t
                LEFT JOIN categorias c ON t.categoria_id = c.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.data_vencimento ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            jsonResponse(['transacoes' => $rows]);

        // ─── SALVAR TRANSAÇÃO ─────────────────────────────────
        case 'salvar':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $id            = intval($data['id'] ?? 0);
            $descricao     = sanitize($data['descricao'] ?? '');
            $valor         = floatval(str_replace(['.', ','], ['', '.'], $data['valor'] ?? 0));
            $tipo          = $data['tipo'] ?? 'despesa';
            $categoria_id  = intval($data['categoria_id'] ?? 0) ?: null;
            $data_venc     = $data['data_vencimento'] ?? '';
            $observacao    = sanitize($data['observacao'] ?? '');
            $parcelas      = intval($data['parcelas'] ?? 1);
            $recorrente    = intval($data['recorrente'] ?? 0);

            if (!$descricao || !$valor || !$data_venc) {
                jsonResponse(['erro' => 'Preencha todos os campos obrigatórios.'], 400);
            }

            if ($id) {
                // Atualizar
                $stmt = $db->prepare("
                    UPDATE transacoes SET
                        descricao=?, valor=?, tipo=?, categoria_id=?,
                        data_vencimento=?, observacao=?, recorrente=?
                    WHERE id=?
                ");
                $stmt->execute([$descricao, $valor, $tipo, $categoria_id, $data_venc, $observacao, $recorrente, $id]);
                jsonResponse(['sucesso' => true, 'mensagem' => 'Transação atualizada!']);
            } else {
                // Inserir (com parcelas)
                $grupo = ($parcelas > 1) ? sprintf('%08x-%04x-%04x', mt_rand(0,0xffffffff), mt_rand(0,0xffff), mt_rand(0,0xffff)) : null;
                $ids = [];

                for ($p = 1; $p <= $parcelas; $p++) {
                    $venc = date('Y-m-d', strtotime($data_venc . " +". ($p-1) . " months"));
                    $desc = ($parcelas > 1) ? "$descricao ($p/$parcelas)" : $descricao;

                    $stmt = $db->prepare("
                        INSERT INTO transacoes
                            (descricao, valor, tipo, categoria_id, data_vencimento, observacao, parcela_atual, total_parcelas, grupo_parcelamento, recorrente)
                        VALUES (?,?,?,?,?,?,?,?,?,?)
                    ");
                    $stmt->execute([$desc, $valor, $tipo, $categoria_id, $venc, $observacao, $p, $parcelas, $grupo, $recorrente]);
                    $ids[] = $db->lastInsertId();
                }

                jsonResponse(['sucesso' => true, 'mensagem' => $parcelas > 1 ? "$parcelas parcelas cadastradas!" : 'Transação cadastrada!', 'ids' => $ids]);
            }

        // ─── PAGAR TRANSAÇÃO ─────────────────────────────────
        case 'pagar':
            $id             = intval($_POST['id'] ?? 0);
            $data_pagamento = $_POST['data_pagamento'] ?? date('Y-m-d');

            if (!$id) jsonResponse(['erro' => 'ID inválido.'], 400);

            $stmt = $db->prepare("UPDATE transacoes SET status='pago', data_pagamento=? WHERE id=?");
            $stmt->execute([$data_pagamento, $id]);
            jsonResponse(['sucesso' => true, 'mensagem' => 'Marcado como pago!']);

        // ─── ESTORNAR PAGAMENTO ───────────────────────────────
        case 'estornar':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) jsonResponse(['erro' => 'ID inválido.'], 400);

            $stmt = $db->prepare("UPDATE transacoes SET status='pendente', data_pagamento=NULL WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['sucesso' => true, 'mensagem' => 'Pagamento estornado!']);

        // ─── EXCLUIR TRANSAÇÃO ────────────────────────────────
        case 'excluir':
            $id    = intval($_POST['id'] ?? 0);
            $grupo = $_POST['grupo'] ?? '';

            if (!$id) jsonResponse(['erro' => 'ID inválido.'], 400);

            if ($grupo) {
                $stmt = $db->prepare("DELETE FROM transacoes WHERE grupo_parcelamento=?");
                $stmt->execute([$grupo]);
                jsonResponse(['sucesso' => true, 'mensagem' => 'Todas as parcelas excluídas!']);
            } else {
                $stmt = $db->prepare("DELETE FROM transacoes WHERE id=?");
                $stmt->execute([$id]);
                jsonResponse(['sucesso' => true, 'mensagem' => 'Transação excluída!']);
            }

        // ─── BUSCAR TRANSAÇÃO ─────────────────────────────────
        case 'buscar':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) jsonResponse(['erro' => 'ID inválido.'], 400);

            $stmt = $db->prepare("SELECT * FROM transacoes WHERE id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            jsonResponse(['transacao' => $row]);

        // ─── CATEGORIAS ───────────────────────────────────────
        case 'categorias':
            $tipo = $_GET['tipo'] ?? '';
            if ($tipo && $tipo !== 'ambos') {
                $stmt = $db->prepare("SELECT * FROM categorias WHERE tipo=? OR tipo='ambos' ORDER BY nome");
                $stmt->execute([$tipo]);
            } else {
                $stmt = $db->query("SELECT * FROM categorias ORDER BY nome");
            }
            jsonResponse(['categorias' => $stmt->fetchAll()]);

        case 'salvar_categoria':
            $data  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id    = intval($data['id'] ?? 0);
            $nome  = sanitize($data['nome'] ?? '');
            $tipo  = $data['tipo'] ?? 'ambos';
            $cor   = $data['cor'] ?? '#6366f1';
            $icone = sanitize($data['icone'] ?? 'tag');

            if (!$nome) jsonResponse(['erro' => 'Nome é obrigatório.'], 400);

            if ($id) {
                $stmt = $db->prepare("UPDATE categorias SET nome=?,tipo=?,cor=?,icone=? WHERE id=?");
                $stmt->execute([$nome, $tipo, $cor, $icone, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO categorias (nome,tipo,cor,icone) VALUES (?,?,?,?)");
                $stmt->execute([$nome, $tipo, $cor, $icone]);
            }
            jsonResponse(['sucesso' => true]);

        case 'excluir_categoria':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $db->prepare("SELECT COUNT(*) FROM transacoes WHERE categoria_id=?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                jsonResponse(['erro' => 'Categoria possui transações vinculadas.'], 400);
            }
            $stmt = $db->prepare("DELETE FROM categorias WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['sucesso' => true]);

        // ─── RELATÓRIO ANUAL ──────────────────────────────────
        case 'relatorio_anual':
            $ano = intval($_GET['ano'] ?? date('Y'));
            $stmt = $db->prepare("
                SELECT
                    MONTH(data_vencimento) as mes,
                    SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END) as receitas,
                    SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END) as despesas
                FROM transacoes
                WHERE YEAR(data_vencimento) = ? AND status != 'cancelado'
                GROUP BY MONTH(data_vencimento)
                ORDER BY mes
            ");
            $stmt->execute([$ano]);
            jsonResponse(['meses' => $stmt->fetchAll()]);

        // ════════════════════════════════════════════
        // MÓDULO EMPRESAS
        // ════════════════════════════════════════════

        // ─── LISTAR EMPRESAS ──────────────────────────────────
        case 'empresas_listar':
            $stmt = $db->query("
                SELECT e.*,
                    (SELECT COUNT(*) FROM clientes c WHERE c.empresa_id = e.id AND c.ativo=1) as total_clientes,
                    (SELECT COUNT(*) FROM servicos s WHERE s.empresa_id = e.id AND s.status='pendente') as servicos_pendentes,
                    (SELECT SUM(s.valor) FROM servicos s WHERE s.empresa_id = e.id AND s.status='pendente') as valor_pendente
                FROM empresas e
                ORDER BY e.nome
            ");
            jsonResponse(['empresas' => $stmt->fetchAll()]);

        case 'empresa_buscar':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM empresas WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['empresa' => $stmt->fetch()]);

        case 'empresa_salvar':
            $data   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id     = intval($data['id'] ?? 0);
            $nome   = sanitize($data['nome'] ?? '');
            $cnpj   = sanitize($data['cnpj'] ?? '');
            $email  = sanitize($data['email'] ?? '');
            $tel    = sanitize($data['telefone'] ?? '');
            $obs    = sanitize($data['observacao'] ?? '');
            if (!$nome) jsonResponse(['erro' => 'Nome da empresa é obrigatório.'], 400);
            if ($id) {
                $stmt = $db->prepare("UPDATE empresas SET nome=?,cnpj=?,email=?,telefone=?,observacao=? WHERE id=?");
                $stmt->execute([$nome,$cnpj,$email,$tel,$obs,$id]);
            } else {
                $stmt = $db->prepare("INSERT INTO empresas (nome,cnpj,email,telefone,observacao) VALUES (?,?,?,?,?)");
                $stmt->execute([$nome,$cnpj,$email,$tel,$obs]);
                $id = $db->lastInsertId();
            }
            jsonResponse(['sucesso' => true, 'id' => $id]);

        case 'empresa_excluir':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM empresas WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['sucesso' => true]);

        // ─── CLIENTES ─────────────────────────────────────────
        case 'clientes_listar':
            $empresa_id = intval($_GET['empresa_id'] ?? 0);
            if (!$empresa_id) jsonResponse(['erro' => 'empresa_id obrigatório'], 400);
            $stmt = $db->prepare("
                SELECT c.*,
                    (SELECT COUNT(*) FROM servicos s WHERE s.cliente_id=c.id AND s.status='pendente') as servicos_pendentes,
                    (SELECT SUM(s.valor) FROM servicos s WHERE s.cliente_id=c.id AND s.status='pendente') as valor_pendente
                FROM clientes c
                WHERE c.empresa_id=? AND c.ativo=1
                ORDER BY c.nome
            ");
            $stmt->execute([$empresa_id]);
            jsonResponse(['clientes' => $stmt->fetchAll()]);

        case 'cliente_buscar':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM clientes WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['cliente' => $stmt->fetch()]);

        case 'cliente_salvar':
            $data       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id         = intval($data['id'] ?? 0);
            $empresa_id = intval($data['empresa_id'] ?? 0);
            $nome       = sanitize($data['nome'] ?? '');
            $email      = sanitize($data['email'] ?? '');
            $tel        = sanitize($data['telefone'] ?? '');
            $obs        = sanitize($data['observacao'] ?? '');
            if (!$nome || !$empresa_id) jsonResponse(['erro' => 'Nome e empresa são obrigatórios.'], 400);
            if ($id) {
                $stmt = $db->prepare("UPDATE clientes SET nome=?,email=?,telefone=?,observacao=? WHERE id=?");
                $stmt->execute([$nome,$email,$tel,$obs,$id]);
            } else {
                $stmt = $db->prepare("INSERT INTO clientes (empresa_id,nome,email,telefone,observacao) VALUES (?,?,?,?,?)");
                $stmt->execute([$empresa_id,$nome,$email,$tel,$obs]);
                $id = $db->lastInsertId();
            }
            jsonResponse(['sucesso' => true, 'id' => $id]);

        case 'cliente_excluir':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE clientes SET ativo=0 WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['sucesso' => true]);

        // ─── SERVIÇOS ─────────────────────────────────────────
        case 'servicos_listar':
            $empresa_id = intval($_GET['empresa_id'] ?? 0);
            $cliente_id = intval($_GET['cliente_id'] ?? 0);
            $status     = $_GET['status'] ?? '';
            $where = ['1=1'];
            $params = [];
            if ($empresa_id) { $where[] = 's.empresa_id=?'; $params[] = $empresa_id; }
            if ($cliente_id) { $where[] = 's.cliente_id=?'; $params[] = $cliente_id; }
            if ($status)     { $where[] = 's.status=?';     $params[] = $status; }
            $stmt = $db->prepare("
                SELECT s.*, c.nome as cliente_nome, e.nome as empresa_nome,
                       DATEDIFF(s.data_vencimento, CURDATE()) as dias_para_vencer
                FROM servicos s
                JOIN clientes c ON s.cliente_id = c.id
                JOIN empresas e ON s.empresa_id = e.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY s.data_vencimento ASC
            ");
            $stmt->execute($params);
            jsonResponse(['servicos' => $stmt->fetchAll()]);

        case 'servico_buscar':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM servicos WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['servico' => $stmt->fetch()]);

        case 'servico_salvar':
            $data       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id         = intval($data['id'] ?? 0);
            $empresa_id = intval($data['empresa_id'] ?? 0);
            $cliente_id = intval($data['cliente_id'] ?? 0);
            $nome       = sanitize($data['nome_servico'] ?? '');
            $descricao  = sanitize($data['descricao'] ?? '');
            $valor      = floatval(str_replace(['.', ','], ['', '.'], $data['valor'] ?? 0));
            $venc       = $data['data_vencimento'] ?? '';
            $recorrente = intval($data['recorrente'] ?? 0);
            if (!$nome || !$empresa_id || !$cliente_id || !$venc) jsonResponse(['erro' => 'Preencha todos os campos obrigatórios.'], 400);
            if ($id) {
                $stmt = $db->prepare("UPDATE servicos SET empresa_id=?,cliente_id=?,nome_servico=?,descricao=?,valor=?,data_vencimento=?,recorrente=? WHERE id=?");
                $stmt->execute([$empresa_id,$cliente_id,$nome,$descricao,$valor,$venc,$recorrente,$id]);
            } else {
                $stmt = $db->prepare("INSERT INTO servicos (empresa_id,cliente_id,nome_servico,descricao,valor,data_vencimento,recorrente) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$empresa_id,$cliente_id,$nome,$descricao,$valor,$venc,$recorrente]);
                $id = $db->lastInsertId();
            }
            jsonResponse(['sucesso' => true, 'id' => $id]);

        case 'servico_pagar':
            $id             = intval($_POST['id'] ?? 0);
            $data_pagamento = $_POST['data_pagamento'] ?? date('Y-m-d');
            $stmt = $db->prepare("UPDATE servicos SET status='pago', data_pagamento=? WHERE id=?");
            $stmt->execute([$data_pagamento, $id]);
            jsonResponse(['sucesso' => true, 'mensagem' => 'Serviço marcado como pago!']);

        case 'servico_estornar':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE servicos SET status='pendente', data_pagamento=NULL WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['sucesso' => true, 'mensagem' => 'Pagamento estornado!']);

        case 'servico_excluir':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM servicos WHERE id=?");
            $stmt->execute([$id]);
            jsonResponse(['sucesso' => true]);

        case 'empresa_resumo':
            $empresa_id = intval($_GET['empresa_id'] ?? 0);
            if (!$empresa_id) jsonResponse(['erro' => 'empresa_id obrigatório'], 400);
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) as total_servicos,
                    SUM(CASE WHEN status='pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status='pago' THEN 1 ELSE 0 END) as pagos,
                    SUM(CASE WHEN status='pendente' THEN valor ELSE 0 END) as valor_pendente,
                    SUM(CASE WHEN status='pago' THEN valor ELSE 0 END) as valor_recebido
                FROM servicos WHERE empresa_id=?
            ");
            $stmt->execute([$empresa_id]);
            jsonResponse(['resumo' => $stmt->fetch()]);

        default:
            jsonResponse(['erro' => 'Ação inválida.'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['erro' => $e->getMessage()], 500);
}
