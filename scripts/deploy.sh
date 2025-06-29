#!/bin/bash

# BoxPos Multi-Tenant Deployment Script
# This script handles zero-downtime deployment for production

set -e

# Configuration
COMPOSE_FILE="docker-compose.production.yml"
APP_NAME="boxpos"
BACKUP_DIR="/backups"
LOG_FILE="/var/log/deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a $LOG_FILE
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a $LOG_FILE
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a $LOG_FILE
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root"
        exit 1
    fi
}

# Backup database
backup_database() {
    log "Creating database backup..."
    
    BACKUP_FILE="$BACKUP_DIR/db_backup_$(date +%Y%m%d_%H%M%S).sql"
    
    docker-compose -f $COMPOSE_FILE exec -T mysql-master mysqldump \
        -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > $BACKUP_FILE
    
    if [ $? -eq 0 ]; then
        log "Database backup created: $BACKUP_FILE"
        
        # Compress backup
        gzip $BACKUP_FILE
        log "Backup compressed: ${BACKUP_FILE}.gz"
        
        # Keep only last 7 days of backups
        find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete
    else
        error "Database backup failed"
        exit 1
    fi
}

# Health check
health_check() {
    local url=$1
    local max_attempts=30
    local attempt=1
    
    log "Performing health check on $url"
    
    while [ $attempt -le $max_attempts ]; do
        if curl -f -s "$url/health" > /dev/null; then
            log "Health check passed"
            return 0
        fi
        
        log "Health check attempt $attempt/$max_attempts failed, retrying..."
        sleep 10
        ((attempt++))
    done
    
    error "Health check failed after $max_attempts attempts"
    return 1
}

# Rolling update
rolling_update() {
    log "Starting rolling update..."
    
    # Get current running services
    SERVICES=$(docker-compose -f $COMPOSE_FILE ps --services | grep app)
    
    for service in $SERVICES; do
        log "Updating service: $service"
        
        # Scale up new instance
        docker-compose -f $COMPOSE_FILE up -d --scale $service=2 --no-recreate
        
        # Wait for new instance to be healthy
        sleep 30
        
        # Scale down old instance
        docker-compose -f $COMPOSE_FILE up -d --scale $service=1 --no-recreate
        
        log "Service $service updated successfully"
    done
}

# Main deployment function
deploy() {
    log "Starting deployment of $APP_NAME"
    
    # Pre-deployment checks
    log "Running pre-deployment checks..."
    
    # Check if docker-compose file exists
    if [ ! -f $COMPOSE_FILE ]; then
        error "Docker compose file not found: $COMPOSE_FILE"
        exit 1
    fi
    
    # Check if .env file exists
    if [ ! -f .env ]; then
        error ".env file not found"
        exit 1
    fi
    
    # Load environment variables
    source .env
    
    # Create backup directory
    mkdir -p $BACKUP_DIR
    
    # Backup database
    backup_database
    
    # Pull latest images
    log "Pulling latest Docker images..."
    docker-compose -f $COMPOSE_FILE pull
    
    # Build application image
    log "Building application image..."
    docker-compose -f $COMPOSE_FILE build app
    
    # Run database migrations
    log "Running database migrations..."
    docker-compose -f $COMPOSE_FILE run --rm app php artisan migrate --force
    
    # Clear caches
    log "Clearing application caches..."
    docker-compose -f $COMPOSE_FILE run --rm app php artisan config:cache
    docker-compose -f $COMPOSE_FILE run --rm app php artisan route:cache
    docker-compose -f $COMPOSE_FILE run --rm app php artisan view:cache
    
    # Rolling update
    rolling_update
    
    # Health check
    if health_check "https://$(hostname)"; then
        log "Deployment completed successfully"
        
        # Send notification (optional)
        # curl -X POST -H 'Content-type: application/json' \
        #     --data '{"text":"BoxPos deployment completed successfully"}' \
        #     $SLACK_WEBHOOK_URL
    else
        error "Deployment failed health check"
        
        # Rollback
        log "Rolling back deployment..."
        docker-compose -f $COMPOSE_FILE down
        docker-compose -f $COMPOSE_FILE up -d
        
        exit 1
    fi
}

# Rollback function
rollback() {
    log "Starting rollback..."
    
    # Get latest backup
    LATEST_BACKUP=$(ls -t $BACKUP_DIR/db_backup_*.sql.gz | head -n1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        error "No backup found for rollback"
        exit 1
    fi
    
    log "Restoring database from: $LATEST_BACKUP"
    
    # Restore database
    gunzip -c $LATEST_BACKUP | docker-compose -f $COMPOSE_FILE exec -T mysql-master \
        mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}
    
    # Restart services
    docker-compose -f $COMPOSE_FILE restart
    
    log "Rollback completed"
}

# Main script logic
case "${1:-deploy}" in
    deploy)
        check_root
        deploy
        ;;
    rollback)
        check_root
        rollback
        ;;
    health-check)
        health_check "${2:-https://$(hostname)}"
        ;;
    *)
        echo "Usage: $0 {deploy|rollback|health-check [url]}"
        exit 1
        ;;
esac
