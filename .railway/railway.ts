import { defineRailway, github, project, service } from "railway/iac";
// This repository manages only its own resources in the environment. Other
// repositories export their own partial name.
// See https://docs.railway.com/infrastructure-as-code#multi-repo-projects
export const partial = "kasirfood";
export default defineRailway(() => {
  const kasirfood = service("kasirfood", {
    source: github("farrelfabiann/kasirfood", { upstreamUrl: "https://github.com/farrelfabiann/kasirfood" }),
    build: {
      buildCommand: "composer install --no-dev --optimize-autoloader && rm -rf /app/node_modules && npm config set cache /tmp/.npm-cache && npm install --no-audit --no-fund && npm run build && rm -rf /app/node_modules/.cache",
      buildEnvironment: "V3",
      builder: "RAILPACK"
    },
    start: "php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT",
    replicas: { "iad": 1 },
  });

  return project("serene-imagination", {
    variables: { managed: false },
    resources: [kasirfood],
  });
});