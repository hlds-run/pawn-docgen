FROM oven/bun:1-slim

WORKDIR /app

# Копируем файлы зависимостей
COPY package.json ./
COPY bun.lock* ./

# Устанавливаем зависимости
RUN bun install --frozen-lockfile

# Копируем исходный код
COPY . .

# Открываем порт
EXPOSE 3000

# Запускаем
CMD ["bun", "run", "start"]