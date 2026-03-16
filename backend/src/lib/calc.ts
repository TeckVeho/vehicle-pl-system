export const REVENUE_CATEGORY = "revenue";
export const EXPENSE_CATEGORY = "expense";
export const SUBTOTAL_REVENUE = "subtotal_revenue";
export const SUBTOTAL_EXPENSE = "subtotal_expense";
export const SUBTOTAL_GROSS = "subtotal_gross";
export const SUMMARY = "summary";

export function isRevenueItem(item: { category: string }): boolean {
  return item.category === REVENUE_CATEGORY;
}

export function isExpenseItem(item: { category: string }): boolean {
  return item.category === EXPENSE_CATEGORY;
}

export function isSubtotalItem(item: { isSubtotal: boolean }): boolean {
  return item.isSubtotal;
}

export function calcNetRevenue(
  amountByItem: Map<string, number>,
  revenueItemIds: Set<string>
): number {
  let sum = 0;
  amountByItem.forEach((amount, itemId) => {
    if (revenueItemIds.has(itemId)) {
      sum += amount;
    }
  });
  return sum;
}

export function calcTotalExpense(
  amountByItem: Map<string, number>,
  expenseItemIds: Set<string>
): number {
  let sum = 0;
  amountByItem.forEach((amount, itemId) => {
    if (expenseItemIds.has(itemId)) {
      sum += amount;
    }
  });
  return sum;
}

export function calcSalesRatio(amount: number, netRevenue: number): number {
  if (netRevenue === 0) return 0;
  return (amount / netRevenue) * 100;
}

export function getCategoryLabel(category: string): string {
  switch (category) {
    case REVENUE_CATEGORY:
      return "売上";
    case EXPENSE_CATEGORY:
      return "原価";
    case SUBTOTAL_REVENUE:
    case SUBTOTAL_EXPENSE:
    case SUBTOTAL_GROSS:
    case SUMMARY:
      return "計";
    default:
      return "";
  }
}
