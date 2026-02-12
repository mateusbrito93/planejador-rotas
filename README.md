# 🗺️ Planejador de Rotas Inteligente

Sistema web desenvolvido para cálculo e comparação de rotas automotivas utilizando a inteligência de dados da **TomTom API**. O projeto foca em segurança, usabilidade e design moderno.

## 🚀 Funcionalidades

- **Comparação de Rotas:** Exibe simultaneamente a rota **Mais Rápida**, a de **Menor Distância** e rotas **Alternativas** (mais lentas/com trânsito).
- **Trânsito em Tempo Real:** Os cálculos consideram engarrafamentos e incidentes no momento da consulta.
- **Segurança de API:** Implementação de arquitetura Backend-Frontend (Proxy em PHP) para ocultar a API Key e impedir uso indevido.
- **UX Aprimorada:**
  - **Autocomplete:** Sugestões de endereço enquanto digita (Fuzzy Search).
  - **Geolocalização:** Centraliza o mapa automaticamente na posição do usuário.
  - **Dark Mode:** Interface e mapa com tema noturno para melhor visualização.

## 🛠️ Tecnologias Utilizadas

- **Frontend:** HTML5, CSS3 (Flexbox/Grid), JavaScript (Vanilla).
- **Backend:** PHP 8+ (cURL para requisições seguras).
- **APIs:** TomTom Maps SDK, Routing API, Search API.
