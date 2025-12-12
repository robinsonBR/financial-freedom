export type BudgetTransaction = {
  categoryId: number | null;
  amount: number;
  date: string; // ISO date string
};

export type CategoryBudget = {
  categoryId: number | null;
  planned: number;
  actual: number;
};

export function aggregateActualByCategory(
  transactions: BudgetTransaction[],
  year: number,
  month: number,
): Record<string, number> {
  const result: Record<string, number> = {};

  for (const tx of transactions) {
    const [yStr, mStr] = tx.date.split('-');
    const y = Number(yStr);
    const m = Number(mStr);

    if (Number.isNaN(y) || Number.isNaN(m)) {
      continue;
    }

    if (y !== year || m !== month) {
      continue;
    }

    const key = String(tx.categoryId ?? 'uncategorized');

    const current = Object.prototype.hasOwnProperty.call(result, key)
      ? result[key]
      : 0;

    result[key] = current + tx.amount;
  }

  return result;
}
