<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planejador de Rotas</title>

    <link rel='stylesheet' type='text/css' href='https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.25.0/maps/maps.css'>
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.25.0/maps/maps-web.min.js"></script>

    <style>
        /* --- DESIGN DARK THEME --- */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
            background-color: #121212;
            color: #e0e0e0;
            overflow: hidden;
        }

        #sidebar {
            width: 380px;
            background: #1e1e1e;
            padding: 30px;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.5);
            z-index: 10;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #333;
        }

        #map {
            flex-grow: 1;
        }

        h2 {
            color: #fff;
            margin-top: 0;
            font-weight: 300;
        }

        h2 span {
            color: #df1b12;
            font-weight: bold;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        label {
            font-size: 0.85em;
            color: #aaa;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px;
            background: #2c2c2c;
            border: 1px solid #444;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #df1b12;
        }

        button.btn-calc {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #df1b12, #ff4d4d);
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s;
        }

        button.btn-calc:hover {
            transform: translateY(-2px);
        }

        .suggestions-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #2c2c2c;
            border: 1px solid #444;
            border-radius: 0 0 8px 8px;
            z-index: 100;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        .suggestions-list div {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid #3a3a3a;
            font-size: 0.9em;
            color: #ddd;
        }

        .suggestions-list div:hover {
            background-color: #3e3e3e;
        }

        /* Cards de Resultado */
        .card {
            background: #252525;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            cursor: pointer;
            border: 1px solid #333;
            transition: transform 0.2s;
            display: none;
            /* Escondido por padrão */
        }

        .card:hover {
            transform: scale(1.02);
            background: #2a2a2a;
        }

        .card.fast {
            border-left: 4px solid #00e676;
        }

        .card.slow {
            border-left: 4px solid #ff3d00;
        }

        .card.short {
            border-left: 4px solid #ffea00;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.7em;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .bg-green {
            background: rgba(0, 230, 118, 0.2);
            color: #00e676;
        }

        .bg-red {
            background: rgba(255, 61, 0, 0.2);
            color: #ff3d00;
        }

        .bg-yellow {
            background: rgba(255, 234, 0, 0.2);
            color: #ffea00;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            font-size: 1.4em;
            font-weight: 700;
            color: #fff;
        }

        .sub-info {
            font-size: 0.85em;
            color: #888;
            margin-top: 5px;
        }

        .footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #333;
            font-size: 0.75em;
            color: #666;
            text-align: center;
        }

        .footer a {
            color: #df1b12;
            text-decoration: none;
            font-weight: bold;
        }

        #loading {
            display: none;
            text-align: center;
            color: #aaa;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div id="sidebar">
        <h2>Planejador de <span>Rotas</span></h2>

        <div class="input-group">
            <label>Ponto A (Origem)</label>
            <input type="text" id="origemInput" placeholder="Digite a origem..." autocomplete="off">
            <input type="hidden" id="origemCoords">
            <div id="origemSuggestions" class="suggestions-list"></div>
        </div>

        <div class="input-group">
            <label>Ponto B (Destino)</label>
            <input type="text" id="destinoInput" placeholder="Digite o destino..." autocomplete="off">
            <input type="hidden" id="destinoCoords">
            <div id="destinoSuggestions" class="suggestions-list"></div>
        </div>

        <button type="button" class="btn-calc" onclick="calcularRotas()">CALCULAR ROTA</button>
        <div id="loading">Calculando melhores trajetos...</div>

        <div id="results-container"></div>

        <div class="footer">
            &copy;
            <?php echo date("Y"); ?> Todos os direitos reservados.<br>
            Desenvolvido por <a href="https://www.linkedin.com/in/mateusbrito93/" target="_blank">Mateus Brito</a>
        </div>
    </div>

    <div id="map"></div>

    <script src="script.js"></script>

</body>

</html>