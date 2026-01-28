import { HmacSecurityProvider } from "./infrastructure/crypto/hmac-security.provider";
import { FontLoaderService } from "./infrastructure/fonts/font-loader.service";
import { TakumiRenderer } from "./infrastructure/renderer/takumi.renderer";

import { GetOgImageHandler } from "./application/queries/get-og-image/get-og-image.handler";
import { GetOgPreviewHandler } from "./application/queries/get-og-preview/get-og-preview.handler";
import { HtmlRenderer } from "./infrastructure/renderer/html.renderer";
import { HealthController } from "./presentation/controllers/health.controller";
import { OgController } from "./presentation/controllers/og.controller";

const config = {
  port: Number(process.env.PORT) || 3000,
  secret: process.env.OG_HMAC_SECRET || "dev-secret-key",
  checkHmac: process.env.CHECK_HMAC === "true",
  checkHmacSymbols: Number(process.env.CHECK_HMAC_SYMBOLS) || 8,
  isDev: process.env.NODE_ENV === "development",
};

const fontLoader = new FontLoaderService();
const securityProvider = new HmacSecurityProvider(
  config.secret,
  config.checkHmac,
  config.checkHmacSymbols,
);
const imageRenderer = new TakumiRenderer(fontLoader);
const htmlRenderer = new HtmlRenderer();

const getOgImageHandler = new GetOgImageHandler(
  imageRenderer,
  securityProvider,
);
const getOgPreviewHandler = new GetOgPreviewHandler(htmlRenderer);

const ogController = new OgController(getOgImageHandler, getOgPreviewHandler);
const healthController = new HealthController(config);

const server = Bun.serve({
  port: config.port,
  routes: {
    "/og": (req) => ogController.render(req),
    "/og/preview": (req) => ogController.preview(req),

    "/health": (req) => healthController.health(req),
  },

  development: config.isDev,

  fetch(req) {
    return new Response("Not Found", { status: 404 });
  },
});

console.log(`
  🚀 OG Generation Service Started
  --------------------------------
  URL:      ${server.url}
  Port:     ${config.port}
  Security: ${config.checkHmac ? "✅ HMAC Enabled" : "⚠️  HMAC Disabled (Dev mode)"}
  isDev:    ${config.isDev ? "🔧 Development" : " Production" + " mode"} 
`);
