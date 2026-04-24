<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <title>Relatorio de Rebanhos por Produtor</title>
        <style>
            body {
                color: #1f2933;
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                margin: 32px;
            }

            h1, h2 {
                margin: 0 0 8px;
            }

            .meta {
                color: #52606d;
                margin-bottom: 20px;
            }

            .farm {
                background: #f5f7f1;
                border: 1px solid #d9e2d0;
                border-radius: 8px;
                margin-bottom: 20px;
                padding: 14px 16px;
            }

            table {
                border-collapse: collapse;
                margin-top: 10px;
                width: 100%;
            }

            th, td {
                border: 1px solid #d9e2d0;
                padding: 8px;
                text-align: left;
            }

            th {
                background: #e9f1de;
            }
        </style>
    </head>
    <body>
        <h1>Relatorio de Rebanhos por Produtor</h1>
        <div class="meta">
            <div><strong>Produtor:</strong> {{ $ruralProducer->name }}</div>
            <div><strong>Documento:</strong> {{ $ruralProducer->cpf_cnpj }}</div>
            <div><strong>Gerado em:</strong> {{ $generatedAt->format('Y-m-d H:i') }}</div>
        </div>

        @foreach ($farms as $farm)
            <section class="farm">
                <h2>{{ $farm['name'] }}</h2>
                <div>{{ $farm['city'] }}, {{ $farm['state'] }}</div>
                <div><strong>Total de animais:</strong> {{ $farm['total_animals'] }}</div>

                <table>
                    <thead>
                        <tr>
                            <th>Especie</th>
                            <th>Finalidade</th>
                            <th>Quantidade</th>
                            <th>Atualizado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($farm['herds'] as $herd)
                            <tr>
                                <td>{{ $herd['species'] ?? '-' }}</td>
                                <td>{{ $herd['purpose'] ?? '-' }}</td>
                                <td>{{ $herd['quantity'] }}</td>
                                <td>{{ $herd['updated_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Nenhum rebanho encontrado para esta fazenda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endforeach
    </body>
</html>
