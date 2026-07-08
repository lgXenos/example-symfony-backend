FROM nginx:alpine

# В Alpine Linux используется пакетный менеджер apk.
# Ставим mc, htop, netstat (в составе busybox или net-tools) и curl для диагностики
RUN apk update && apk add --no-cache \
    mc \
    htop \
    net-tools \
    curl
