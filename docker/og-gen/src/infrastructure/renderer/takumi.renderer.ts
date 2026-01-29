import { ImageResponse } from "@takumi-rs/image-response";
import { Children, createElement, isValidElement, ReactNode } from "react";
import { OgImage } from "../../domain/entities/og-image.entity";
import { ImageRenderer } from "../../domain/interfaces/image-renderer.interface";
import { OgTemplate } from "../../presentation/components/og-template.component";
import { FontLoaderService } from "../fonts/font-loader.service";

export class TakumiRenderer implements ImageRenderer<Uint8Array> {
  constructor(private readonly fontLoader: FontLoaderService) {}

  async render(image: OgImage): Promise<Uint8Array> {
    const [fontRegular, fontSemi] = await Promise.all([
      this.fontLoader.getFontData("IBMPlexSans-Regular.ttf"),
      this.fontLoader.getFontData("IBMPlexSans-SemiBold.ttf"),
    ]);

    const options = {
      width: image.getDimensions().width,
      height: image.getDimensions().height,
      fonts: [
        { name: "IBMPlex", data: fontRegular, weight: 400, style: "normal" },
        { name: "IBMPlex", data: fontSemi, weight: 600, style: "normal" },
      ],
    };

    try {
      const takumiTree = TakumiRenderer.toTakumiNodes(
        createElement(OgTemplate, { image }),
      );

      const response = new ImageResponse(takumiTree, options);
      return new Uint8Array(await response.arrayBuffer());
    } catch (error: any) {
      console.error("[Takumi Error]:", error.message);
      throw new Error(`Failed to generate PNG: ${error.message}`);
    }
  }

  static toTakumiNodes(node: ReactNode): ReactNode {
    if (!isValidElement(node)) {
      return node;
    }

    const element = node as any;
    const type = element.type;
    const props = element.props;

    if (typeof type === "function") {
      const rendered = (type as any)(props);
      return this.toTakumiNodes(rendered);
    }

    const newProps: any = { ...props };

    if (props.className) {
      newProps.tw = props.className;
      delete newProps.className;
    }

    if (props.children) {
      newProps.children = Children.map(props.children, (child) =>
        this.toTakumiNodes(child),
      );
    }

    return createElement(type as any, newProps);
  }
}
