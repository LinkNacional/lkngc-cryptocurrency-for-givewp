# 4.0.4 - 18/11/2026
* Corrigido o gateway Ethereum, que consultava a cadeia do Bitcoin (doações em ETH nunca eram confirmadas automaticamente);
* Corrigida a verificação da constante de versão, que servia todos os assets com a versão fixa 1.0.0 (cache quebrado após atualização);
* Corrigida a versão passada ao construtor da classe de admin;
* Corrigido o bloco de detalhes da doação, que renderizava vazio (o load_template não expõe o terceiro argumento ao template);
* Corrigida a consulta do template do formulário, que tinha o prefixo de tabela do WordPress fixo em "wp_";
* Corrigida a rota REST de verificação, que matava a resposta JSON (exit) em requisições sem parâmetros e quebrava com doação inexistente ou API fora do ar;
* A desinstalação agora remove apenas as opções do plugin e não quebra se o GiveWP já foi removido;
* Limpeza de padrão de código e remoção dos acessos obsoletos apontados pelo Plugin Check;
* Versão mínima de PHP elevada para 8.1, alinhada no cabeçalho do plugin, no readme e nos workflows de release;
* readme reescrito, com serviços externos, bibliotecas e configuração documentados;
* Removido o boilerplate npm/eslint que sobrou do template (o plugin não tem build de JavaScript e nunca usou esses pacotes), então o pacote não leva mais package.json e as dependências desatualizadas apontadas pelo Dependabot deixam de existir.

# 4.0.3 - 12/03/2026
* Adição de novos ícones e banners.

# 4.0.2 - 11/02/2025
* Adequação as normativas do WordPress;
* Correção de erros de script;
* Atualização de slug para lkngc-cryptocurrency-for-givewp.

# 4.0.1 - 03/02/2025
* Adequação as normativas do WordPress

# 4.0.0 - 16/09/2024

* Refatoração completa do plugin com uso de boilerplate;
* Implementação de boilerplate para padronização e melhores práticas no desenvolvimento;
* Compatibilidade com o Give 3.0;
* Correção na geração do QrCode;
* Otimização e limpeza geral do código para melhor desempenho e manutenção;
* Implementação de caching de consultas para otimizar o tempo e reduzir o número de requisições à API.

# 3.0.0 - 05/12/2023

* Refatoração completa para boas práticas;
* Integração ao novo formulário do GiveWP v3.0.0;
* Adição de configuração para margem de erro, usada nas verificações de pagamento;
* Correção de bug causando multiplas requisições à API.

# 2.1.2

* Correção de tradução faltante
* Correção no tratamento de casas decimais
* Correção no tratamento de casas decimais com PagHiper

# 2.1.1

* Correção de bug de arquivos não importados;
* Melhoria na função de ativação do plugin.

# 2.1.0

* Implementar tradução para inglês e português;
* Corrigir compatibilidade com multi-moedas;
* Adição de compatibilidade com cobranças em Bitcoin nativas do GiveWP.

# 2.0.0

* Refatoração dos metadados de doação;
* Adicionar sanitização de atributos;
* Limpeza e otimização de código;
* Alteração do endpoint para atualizações automáticas.

# 1.1.1

* Correção de bug de script de verificação de licença.

# 1.1.0

* Adicionada opção de ativar e cadastrar carteira Waves;
* Ajustes menores de visualização;
* Atualização de metadados da doação.

# 1.0.0

* Lançamento de plugin.
