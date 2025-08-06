# Use official WordPress image as base
FROM wordpress:latest

# Fix Debian sources, disable expired metadata check, then install packages
RUN apt-get update && \
    apt-get install -y nano && \
    rm -rf /var/lib/apt/lists/*


# Optional: set working directory to wordpress install location
WORKDIR /var/www/html

# Default command is the wordpress image entrypoint and cmd