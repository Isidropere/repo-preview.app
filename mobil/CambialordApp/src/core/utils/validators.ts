export const isValidEmail = (email: string): boolean => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
};

export const isValidPhone = (phone: string): boolean => {
  const re = /^[0-9]{10}$/;
  return re.test(phone.replace(/\D/g, ''));
};

export const isRequired = (value: string): boolean => {
  return value.trim().length > 0;
};

export const minLength = (value: string, min: number): boolean => {
  return value.length >= min;
};
