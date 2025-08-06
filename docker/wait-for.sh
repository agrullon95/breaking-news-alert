#!/bin/bash
host="$1"
port="$2"
shift 2

echo "Waiting for MySQL at $host:$port..."
until nc -z "$host" "$port"; do
  echo "Still waiting for $host:$port..."
  sleep 2
done

echo "MySQL is up - executing command"
exec "$@"
