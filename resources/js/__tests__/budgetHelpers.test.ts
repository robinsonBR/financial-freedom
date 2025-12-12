import { describe, expect, it } from 'vitest';
import { calculateUtilization } from '../Domain/budget/utilization';

describe('calculateUtilization', () => {
  it('returns 0 when planned is 0', () => {
    expect(calculateUtilization({ planned: 0, actual: 100 })).toBe(0);
  });

  it('returns fraction of actual over planned, capped at 1', () => {
    expect(calculateUtilization({ planned: 100, actual: 50 })).toBe(0.5);
    expect(calculateUtilization({ planned: 100, actual: 100 })).toBe(1);
    expect(calculateUtilization({ planned: 100, actual: 150 })).toBe(1);
  });
});
