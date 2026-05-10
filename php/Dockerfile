# =============================================================================
# Multi-Stage Symfony Application Dockerfile (Optimized)
# =============================================================================

# ============================================================================
# STAGE 1: php-base - Base image with essential PHP extensions
# ============================================================================
FROM php:8.1-fpm AS php-base

# Install system dependencies and PHP extensions in one layer
RUN apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    netcat-traditional \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# ============================================================================
# STAGE 2: builder - Build dependencies (Composer, npm, assets)
# ============================================================================
FROM php-base AS builder

# Install build tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    nodejs \
    npm \
    build-essential \
    autoconf \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY src .

# Install PHP dependencies with optimized autoloader
# Use --no-scripts to avoid running cache:clear before DB is available
RUN composer install \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# Install npm dependencies and build assets
RUN npm ci && npm run build

# ============================================================================
# STAGE 3: runtime - Production image (minimal)
# ============================================================================
FROM php-base AS runtime

# Install mysql client for health checks
RUN apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy only necessary artifacts from builder
COPY --from=builder /app/vendor /app/vendor
COPY --from=builder /app/node_modules /app/node_modules
COPY --from=builder /app/public/build /app/public/build
COPY src .
COPY php/entrypoint.sh /entrypoint.sh
COPY php/wait-for-it.sh /wait-for-it.sh

# Create necessary directories with proper permissions
RUN mkdir -p var/log var/cache && chmod -R 777 var && chmod +x /entrypoint.sh /wait-for-it.sh

EXPOSE 8000

# Health check (using basic PHP check instead of curl)
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD php -r "exit(file_exists('var/cache') ? 0 : 1);" || exit 1

# Run entrypoint script that waits for DB before starting FPM
ENTRYPOINT ["/entrypoint.sh"]

# ============================================================================
# STAGE 4: dev - Development image with all tools
# ============================================================================
FROM builder AS dev

# Install additional development tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    bash-completion \
    vim \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Symfony CLI (dev only)
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/bin/symfony

# Install dev dependencies for composer
RUN composer install \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# Create cache directories and copy entrypoint
COPY php/entrypoint.sh /entrypoint.sh
COPY php/wait-for-it.sh /wait-for-it.sh

RUN mkdir -p var/log var/cache && chmod -R 777 var && chmod +x /entrypoint.sh /wait-for-it.sh

EXPOSE 8000

# Use entrypoint for dev too (runs cache:clear when DB is ready)
ENTRYPOINT ["/entrypoint.sh"]