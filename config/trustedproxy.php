<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Lista separada por vírgulas dos IPs/redes que fazem terminação TLS à
    | frente da aplicação (nginx, Apache, load balancer da cloud). Aceita
    | também CIDR e o literal `*` para "qualquer um".
    |
    | Vazio (ou ausente) = não confiar em nenhum — é o caso de desenvolvimento
    | e de testes.
    |
    | Porquê importa: com um proxy à frente, o PHP vê o pedido como http:// e
    | `$request->secure()` devolve false. As consequências nesta aplicação são
    | concretas —
    |
    |   - o `SecurityHeaders` só emite `Strict-Transport-Security` em HTTPS,
    |     por isso **o HSTS nunca sairia** (ver app/Http/Middleware/
    |     SecurityHeaders.php);
    |   - redirects e URLs geradas sairiam em `http://`.
    |
    | O `TrustProxies` do framework já vem registado na pilha global por
    | omissão e lê esta chave **no momento do pedido** (não no bootstrap, onde
    | o repositório de config ainda não existe) — por isso este ficheiro é
    | lido com `env()` normalmente, mesmo com `config:cache` activo.
    |
    | Em produção: TRUSTED_PROXIES=127.0.0.1 (proxy na mesma máquina) ou o IP
    | privado do proxy (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16).
    | Ver gmag/DEPLOY.md.
    |
    */

    'proxies' => env('TRUSTED_PROXIES') ?: null,

];
