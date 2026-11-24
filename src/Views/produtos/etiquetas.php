<?php
use Picqer\Barcode\BarcodeGeneratorSVG;
$generator = new BarcodeGeneratorSVG();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Etiquetas de Produtos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .etiquetas-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        /* Tamanho padrão de etiqueta (ex: Pimaco) - 3 colunas por folha A4 */
        .etiqueta {
            width: 6.5cm;
            height: 3.5cm;
            background: #fff;
            border: 1px solid #ccc;
            padding: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-sizing: border-box;
        }

        .etiqueta img {
            width: 100%;
            height: 1.3cm;
            object-fit: contain;
            margin-bottom: 2px;
        }

        .etiqueta .codigo {
            font-weight: bold;
            font-size: 12px;
        }

        .etiqueta .descricao {
            font-size: 9px;
            text-transform: uppercase;
            overflow: hidden;
            max-height: 22px;
            line-height: 1.1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .no-print {
            margin-bottom: 20px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .etiquetas-container {
                gap: 5px;
            }

            /* Ajuste o gap para não quebrar na margem */
            .etiqueta {
                border: 1px dashed #ccc;
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 1cm;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #0088B7; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">🖨️
            Imprimir Etiquetas</button>
        <a href="?controller=produto&action=list"
            style="padding: 10px 20px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 5px; margin-left: 10px;">Voltar</a>
    </div>

    <div class="etiquetas-container">
        <?php foreach ($produtos as $p):
            // Gera o código de barras em SVG
            $barcode = $generator->getBarcode($p['codigo'], $generator::TYPE_CODE_128, 1.2, 40);
            $barcodeBase64 = base64_encode($barcode);
            ?>
            <div class="etiqueta">
                <!-- Insere o SVG em base64 para evitar problemas de renderização -->
                <img src="data:image/svg+xml;base64,<?= $barcodeBase64 ?>" alt="<?= $p['codigo'] ?>">
                <div class="codigo"><?= $p['codigo'] ?></div>
                <div class="descricao"><?= $p['descricao'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>