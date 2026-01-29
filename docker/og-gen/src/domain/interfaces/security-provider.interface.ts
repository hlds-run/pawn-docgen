export interface SecurityProvider {
  verify(data: string, hash: string): boolean;
  generateHash(data: string): string;
}
