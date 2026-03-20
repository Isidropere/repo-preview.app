export const formatPrice = (price: number): string => {
  return `RD$ ${price.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
};

export const formatDate = (dateStr: string): string => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-DO', {day: '2-digit', month: 'short', year: 'numeric'});
};

export const truncateText = (text: string, maxLength: number): string => {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};
