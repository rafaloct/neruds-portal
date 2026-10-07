# Development-only edge proxy for the isolated recipe. See edge-nginx.conf.
FROM nginx:1.27-alpine

COPY infra/dev/edge-nginx.conf /etc/nginx/conf.d/default.conf
