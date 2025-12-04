FROM nginx:alpine

# Install dependencies
RUN apk add --no-cache \
    curl \
    bash

# Copy Nginx configuration
COPY docker/web/nginx.conf /etc/nginx/conf.d/default.conf

# Set working directory
WORKDIR /var/www/html

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

CMD ["nginx", "-g", "daemon off;"]
