# pix-test

## Iniciando docker
Mysql e MailHog serão instalados e subirão durante a inicialização
```
docker-compose up -d --build
```

## Iniciando Hyperf
```
docker exec -it saque_pix_app php bin/hyperf.php start
```


## App
- Criação de enum para registrar os status
- Utilizo Mac, então precisei adicionar a plataforma no MailHog no docker-compose para que o warning de incompatibilidade parasse de aparecer. Pode ser que a mesmo warning apareça dependendo do seu equipamento

## Emails
- O envio de e-mail foi implementado de forma assíncrona utilizando o sistema de tasks do Hyperf, evitando impacto na latência da requisição.
- O histórico de emails enviados pode ser visto em localhost:8025

## Saques agendados
- São processados por um job recorrente


## Banco de dados
`` account_withdraw ``
- A coluna `status` foi adicionada para facilitar a interação com o fluxo de saque, mantendo compatibilidade com os campos originais (`done`, `error`) exigidos no case.

`` account_withdraw_pix ``
- Coluna key alterada para pix_key porque key é uma palavra reservada em alguns contextos
