# Use official WordPress image as base
FROM wordpress:latest

# Install netcat and nano
RUN apt-get update && \
    apt-get install -y netcat-openbsd nano && \
    rm -rf /var/lib/apt/lists/*

# Wait for MySQL before starting WordPress
COPY wait-for.sh /wait-for.sh
RUN chmod +x /wait-for.sh

ENTRYPOINT ["/wait-for.sh", "db", "3306", "--", "docker-entrypoint.sh"]
CMD ["apache2-foreground"]

# Fix Debian sources, disable expired metadata check, then install packages
RUN apt-get update && \
    apt-get install -y nano && \
    rm -rf /var/lib/apt/lists/*


# Optional: set working directory to wordpress install location
WORKDIR /var/www/html

# Default command is the wordpress image entrypoint and cmd