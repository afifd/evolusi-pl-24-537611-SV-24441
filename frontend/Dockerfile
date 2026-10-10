# ===================================================
# Stage 1: Build Frontend Assets (Node 24 Alpine)
# ===================================================
FROM docker.io/library/node:24-alpine AS builder

WORKDIR /app

# Cache dependency layer first
COPY package.json package-lock.json ./
RUN npm ci

# Copy source code and build
COPY . .
ARG VITE_API_URL=http://localhost:8000/api
ENV VITE_API_URL=$VITE_API_URL
RUN npm run build

# ===================================================
# Stage 2: Serve via Nginx (Alpine)
# ===================================================
FROM docker.io/library/nginx:alpine AS runner

# Remove default nginx html
RUN rm -rf /usr/share/nginx/html/*

# Copy built SPA dist from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy custom Nginx SPA configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Healthcheck for nginx container
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget -q -O /dev/null http://localhost/ || exit 1

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
