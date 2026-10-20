---
description: Roda Pint, PHPStan e a suíte de testes e corrige o que falhar
---

# Verificação de qualidade

1. Formate o código:
   `docker compose exec app ./vendor/bin/pint`
2. Análise estática:
   `docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M`
   Corrija os erros no código. Não adicione `ignoreErrors` nem `@phpstan-ignore` sem perguntar ao usuário.
3. Testes:
   `docker compose exec app php artisan test`
   Se algo falhar, leia a saída, corrija a causa (não o teste, a menos que o teste esteja errado) e rode de novo.
4. Relate o resultado real de cada etapa ao usuário, incluindo falhas que não puderam ser resolvidas.

Se os containers não estiverem de pé, peça ao usuário para rodar `docker compose up -d` em vez de tentar rodar as ferramentas fora do Docker.
