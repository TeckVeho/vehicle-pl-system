module.exports = {
  apps: [
    {
      name: "vehicle-pl-system-be-dev",
      script: "dist/index.js",
      cwd: "/var/www/vehicle-pl-system/backend",
      env: {
        PORT: 4000,
        NODE_ENV: "development",
        DATABASE_URL: "mysql://izumi:TwM$pULFm59O0dbKT@dev.ckhn4ht6bna3.ap-northeast-1.rds.amazonaws.com:3306/izumi_vehicle_pl_system?sslaccept=accept_invalid_certs",
        CORS_ORIGIN: "https://izumi-vpl.vw-dev.com",
        JWT_SECRET: "4ZN8BxOOpaRoP4FqWv1HTrwFecOg4PFS4zCUiQExFsp2FT49FFyEknMVpCi3DV05"
      }
    },
    {
      name: "vehicle-pl-system-fe-dev",
      script: "npm",
      args: "run start -- --port 4001",
      cwd: "/var/www/vehicle-pl-system",
      env: {
        NODE_ENV: "production"
      }
    }
  ]
};
