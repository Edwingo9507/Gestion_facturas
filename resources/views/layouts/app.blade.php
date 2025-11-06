<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal de Consulta y Pago de Facturas')</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <style>
        /* ====== Estilos Globales ====== */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }

        main {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* ====== Formularios y Botones ====== */
        .consulta-form h2, .resultados-tabla h2 {
            color: #0056b3;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
        }

        .consulta-form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        .consulta-form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* ====== Tabla de Facturas ====== */
        .resultados-tabla table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .resultados-tabla th, .resultados-tabla td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .resultados-tabla th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .vencida {
            color: red;
            font-weight: bold;
        }

        .btn-pagar {
            background-color: #28a745;
        }

        .btn-pagar:hover {
            background-color: #1e7e34;
        }

        /* ====== Resumen de Pago ====== */
        .resumen-pago {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9f2ff;
            border: 1px solid #cce5ff;
            text-align: right;
        }

        .btn-checkout {
            padding: 12px 20px;
            font-size: 1.1em;
            background-color: #ffc107;
            color: #333;
            font-weight: bold;
        }

        .btn-checkout:hover {
            background-color: #e0a800;
        }

        /* ====== Footer ====== */
        footer {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            background-color: #333;
            color: white;
            font-size: 0.9em;
        }

    .estado.pagada { color: green; font-weight: bold; }
    .estado.pendiente { color: orange; font-weight: bold; }
    .estado.vencida { color: red; font-weight: bold; }
    table input[type="checkbox"] {
        transform: scale(1.2);
    }
    .acciones {
        margin-top: 15px;
        text-align: right;
    }
    #btnPagar {
        background-color: #2b78e4;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 5px;
        cursor: pointer;
    }
    #btnPagar:hover {
        background-color: #1a5dbb;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

/* estilo para la vista de cofirmacion */
section {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* === Tablas === */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

table th {
    background-color: #f4f4f4;
    font-weight: bold;
}

/* === Botón principal === */
.btn-pagar {
    display: block;
    margin: 25px auto 0;
    background: #007bff;
    color: white;
    border: none;
    padding: 12px 25px;
    font-size: 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-pagar:hover {
    background: #0056b3;
}

/* === Totales === */
.total {
    text-align: right;
    font-size: 1.1em;
    margin-top: 10px;
}

    </style>
</head>
<body>

    <header>
        <h1>Portal de Pagos en Línea</h1>
        <nav>
            <a href="#">Ayuda</a>
            <a href="#">Contacto</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Empresa. Pagos Seguros.</p>
    </footer>

</body>
</html>
