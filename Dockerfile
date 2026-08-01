# ─────────────────────────────────────────────────────────────
# Matka Result Portal — FrankenPHP on Railway
# No Apache. Uses FrankenPHP (Caddy + PHP in one binary).
# ─────────────────────────────────────────────────────────────
FROM dunglas/frankenphp:latest-php8.3-alpine

# ─── Install required PHP extensions ─────────────────────────
# pdo is compiled into PHP by default.
# We only need the MySQL driver + mysqli.
RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    opcache

# ─── PHP production config ────────────────────────────────────
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# OPcache tuning for production
RUN printf "\nopcache.enable=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=4000\nopcache.validate_timestamps=0\n" \
    >> "$PHP_INI_DIR/php.ini"

# ─── App setup ───────────────────────────────────────────────
WORKDIR /app

# Copy everything
COPY . .

# ─── Runtime ─────────────────────────────────────────────────
# Railway injects $PORT at runtime (usually 8080).
# SERVER_NAME tells FrankenPHP which address to bind.
# The Caddyfile also reads {$PORT:8080} for the listener.
EXPOSE 8080

CMD ["sh", "-c", "SERVER_NAME=\":${PORT:-8080}\" frankenphp run --config /app/Caddyfile"]
