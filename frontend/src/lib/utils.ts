import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { format, isToday, isYesterday } from 'date-fns';
import { pl } from 'date-fns/locale';


export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export const formatDateNicely = (date: Date, short?: boolean): string => {
  date = new Date(date);

  const today = new Date();

  if (isToday(date)) {
    return 'Dzisiaj';
  }

  if (isYesterday(date)) {
    return 'Wczoraj';
  }

  const twoDaysAgo = new Date();
  twoDaysAgo.setDate(today.getDate() - 2);
  const isTwoDaysAgo =
    date.getDate() === twoDaysAgo.getDate() &&
    date.getMonth() === twoDaysAgo.getMonth() &&
    date.getFullYear() === twoDaysAgo.getFullYear();

  if (isTwoDaysAgo) {
    return 'Przedwczoraj';
  }

  if(date.getFullYear() !== today.getFullYear()) {
    if(short) return format(date, "d MMM yyyy", { locale: pl });
    return format(date, "d MMMM yyyy", { locale: pl });
  }

  if(short) return format(date, "d MMM", { locale: pl });
  return format(date, "d MMMM", { locale: pl }); 
};

export const formatAmount = (amount: number, currency: string): string => {
  try{
    const currencyFormat = new Intl.NumberFormat('pl-PL', {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    return currencyFormat.format(amount);
  } catch (e) {
    return `${amount.toFixed(2)} ${currency}`;
  }
}