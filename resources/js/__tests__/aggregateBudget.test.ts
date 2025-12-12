import { describe, expect, it } from 'vitest';
import { aggregateActualByCategory, type BudgetTransaction } from '../Domain/budget/aggregateBudget';

describe('aggregateActualByCategory', () => {
  it('ignores transactions outside the given month and year', () => {
    const txs: BudgetTransaction[] = [
      { categoryId: 1, amount: 50, date: '2024-01-05' },
      { categoryId: 1, amount: 25, date: '2024-02-10' },
    ];

    const result = aggregateActualByCategory(txs, 2024, 1);

    expect(result['1']).toBe(50);
    expect(result['2']).toBeUndefined();
  });

  it('aggregates amounts per category id and handles uncategorized', () => {
    const txs: BudgetTransaction[] = [
      { categoryId: 1, amount: 10, date: '2024-01-01' },
      { categoryId: 1, amount: 15, date: '2024-01-15' },
      { categoryId: 2, amount: 5, date: '2024-01-20' },
      { categoryId: null, amount: 7, date: '2024-01-22' },
    ];

    const result = aggregateActualByCategory(txs, 2024, 1);
    // console.log('aggregateActualByCategory result', result);

    expect(result['1']).toBe(25);
    expect(result['2']).toBe(5);
    expect(result['uncategorized']).toBe(7);
  });
});
