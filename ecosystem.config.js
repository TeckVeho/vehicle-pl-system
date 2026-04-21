const dotenv = require('dotenv');
const path = require('path');

// Đọc biến từ file .env của backend
const envConfig = dotenv.config({
  path: path.resolve(__dirname, 'backend/.env')
}).parsed || {};

module.exports = {
  apps: [
    {
      name: "vehicle-pl-system-be-dev",
      script: "dist/index.js",
      cwd: path.resolve(__dirname, 'backend'),
      env: {
        PORT: envConfig.PORT || 4000,
        NODE_ENV: envConfig.NODE_ENV || "development",
        DATABASE_URL: envConfig.DATABASE_URL,
        CORS_ORIGIN: envConfig.CORS_ORIGIN,
        JWT_SECRET: envConfig.JWT_SECRET
      }
    },
    {
      name: "vehicle-pl-system-fe-dev",
      script: "npm",
      args: "run start -- --port 4001",
      cwd: __dirname,
      env: {
        NODE_ENV: "production"
      }
    }
  ]
};
