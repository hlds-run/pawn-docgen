import { GetOgImageHandler } from "../../application/queries/get-og-image/get-og-image.handler";
import { GetOgImageQuery } from "../../application/queries/get-og-image/get-og-image.query";
import { GetOgPreviewHandler } from "../../application/queries/get-og-preview/get-og-preview.handler";
import { OgImage } from "../../domain/entities/og-image.entity";
import { Theme } from "../../domain/entities/theme.entity";

export class OgController {
  constructor(
    private readonly getOgImageHandler: GetOgImageHandler,
    private readonly getOgPreviewHandler: GetOgPreviewHandler,
  ) {}

  /* 
    GET /og
    Entrypoint
  */
  async render(req: Request): Promise<Response> {
    const url = new URL(req.url);

    const title = url.searchParams.get("title") || "";
    const subtitle = url.searchParams.get("subtitle") || "";
    const tag = url.searchParams.get("tag") || "Pawn";
    const themeStr = url.searchParams.get("theme");

    const signature = url.searchParams.get("s") || "";

    try {
      const theme = Theme.fromString(themeStr);
      const ogImage = new OgImage({ title, subtitle, tag, theme });

      const imageBuffer = await this.getOgImageHandler.execute(
        new GetOgImageQuery(ogImage, signature),
      );

      return new Response(Buffer.from(imageBuffer), {
        headers: {
          "Content-Type": "image/png",
          "Cache-Control": "public, max-age=31536000, immutable",
          "X-Content-Type-Options": "nosniff",
        },
      });
    } catch (error: any) {
      const isAuthError = error.message === "Invalid signature";

      return new Response(error.message, {
        status: isAuthError ? 403 : 500,
      });
    }
  }

  /* 
    GET /og/preview
    Entrypoint
  */
  async preview(req: Request): Promise<Response> {
    const url = new URL(req.url);

    const title = url.searchParams.get("title") || "";
    const subtitle = url.searchParams.get("subtitle") || "";
    const tag = url.searchParams.get("tag") || "Pawn";
    const themeStr = url.searchParams.get("theme");

    try {
      const theme = Theme.fromString(themeStr);
      const ogImage = new OgImage({ title, subtitle, tag, theme });

      const imageBuffer = await this.getOgPreviewHandler.execute(
        new GetOgImageQuery(ogImage, ""),
      );

      return new Response(Buffer.from(imageBuffer), {
        headers: { "Content-Type": "text/html; charset=utf-8" },
      });
    } catch (error: any) {
      const isAuthError = error.message === "Invalid signature";

      return new Response(error.message, {
        status: isAuthError ? 403 : 500,
      });
    }
  }
}
