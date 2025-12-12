export type UtilizationInput = {
  planned: number;
  actual: number;
};

// Returns a value between 0 and 1 (inclusive).
export function calculateUtilization({ planned, actual }: UtilizationInput): number {
  if (!planned || planned <= 0) {
    return 0;
  }

  const ratio = actual / planned;

  if (!Number.isFinite(ratio) || ratio <= 0) {
    return 0;
  }

  return ratio > 1 ? 1 : ratio;
}
