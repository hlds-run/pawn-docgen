import type { ReactElement } from "react";
import { OgImage } from "../../domain/entities/og-image.entity";

export const OgTemplate = ({ image }: { image: OgImage }): ReactElement => {
  const { title, subtitle, tag, theme } = image;
  const { bg, card, border, text, mutedText, accent, pillBg, pillText } =
    theme.getColors();

  return (
    <div className={`flex w-full h-full ${bg} p-[60px]`}>
      {/* Main Card */}
      <div
        className={`flex flex-col w-full h-full ${card} rounded-r-[28px] border ${border} p-[40px] relative`}
      >
        {/* Left Accent Stripe */}
        <div
          className={`absolute left-0 top-0 bottom-0 w-[10px] ${accent} rounded-l-[28px]`}
        />
        {/* Tag Flat Pill Badge */}
        <div
          className={`flex ${pillBg} border border-opacity-20 px-[16px] py-[4px] rounded-full self-start mb-[35px]`}
        >
          <span
            className={`${pillText} text-[14px] font-bold uppercase tracking-wider`}
          >
            {tag}
          </span>
        </div>
        {/* Title */}
        <div
          className={`flex text-[48px] font-semibold ${text} leading-[1.2] mb-[20px]`}
        >
          {title}
        </div>
        {/* Subtitle */}
        <div
          className={`flex text-[24px] font-normal ${mutedText} leading-[1.4]`}
        >
          {subtitle}
        </div>
        {/* Footer */}
        <div
          className={`absolute right-[40px] bottom-[40px] flex ${mutedText} text-[18px] font-semibold`}
        >
          pawn-docgen
        </div>
      </div>
    </div>
  );
};
