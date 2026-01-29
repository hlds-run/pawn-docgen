import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";
import { OgImage } from "../../domain/entities/og-image.entity";
import { OgLayout } from "../../presentation/components/og-layout.component";
import { OgTemplate } from "../../presentation/components/og-template.component";

export class HtmlRenderer {
  async render(image: OgImage): Promise<string> {
    const previewTree = createElement(
      OgLayout,
      null,
      createElement(OgTemplate, { image }),
    );

    const html = renderToStaticMarkup(previewTree);

    return `<!DOCTYPE html>${html}`;
  }
}
