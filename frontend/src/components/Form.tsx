import { useEffect, useState } from 'react';
import { View } from 'react-native';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import FormField from '~/components/FormItem';
import Loading from '~/components/Loading';
import { ApiError } from '~/api/types';
import { SerializedError } from '@reduxjs/toolkit';

export type FormFieldType = {
  label: string;
  name: string;
  type: 'text' | 'number' | 'textarea' | 'password';
  required?: boolean;
  placeholder?: string;
}

type FormProps = {
  fields: FormFieldType[];
  onSubmit: (data: { [key: string]: string | number }) => void;
  isLoading?: boolean;
  error?: ApiError | SerializedError;
  submitText?: string;
}

export type FormDataType = {
  [key: string]: string | number;
}

type ErrorType = {
  [key: string]: string | undefined;
}

const Form = ({ fields, onSubmit, error, isLoading, submitText }: FormProps) => {
  const [formData, setFormData] = useState<FormDataType>({});
  const [errors, setErrors] = useState<ErrorType>({});
  const [generalError, setGeneralError] = useState<string | null>();

  useEffect(() => {
    setGeneralError(null);

    if (!error) return;

    if ('status' in error) {
      const errorMap: ErrorType = {};
      
      error.violations?.forEach(violation => {
        const fieldName = violation.propertyPath;
        errorMap[fieldName] = violation.message;
      });
      
      setErrors(errorMap);

      if (error.detail && !error.violations) {
        setGeneralError(error.detail);
      }
    } else if ('message' in error && error.message) {
      setGeneralError(error.message);
    } else {
      setGeneralError('Wystąpił nieoczekiwany błąd');
    }
  }, [error]);

  const handleChange = (name: string, value: string | number) => {
    setFormData(prev => ({ ...prev, [name]: value }));
    setErrors(prev => ({...prev, [name]: undefined}));
  };

  const handleSubmit = () => {
    let isValid = true;
    const newErrors: ErrorType = {};
    for (const field of fields) {
      if (!formData[field.name] && field.required) {
        newErrors[field.name] = 'To pole jest wymagane';
        isValid = false;
      }
    }
    if (!isValid) {
      setErrors(newErrors);
      return;
    }
    onSubmit(formData);
  };

  return (
    <View>
      {generalError && (
        <View className="mb-4 p-3 bg-red-100 rounded">
          <Text className="text-red-700">{generalError}</Text>
        </View>
      )}
      {fields.map((field) => (
        <View key={field.name}>
          <FormField
            field={field}
            value={formData[field.name]?.toString()}
            onChange={handleChange}
            error={errors[field.name]}
          />
        </View>
      ))}
      <Button 
        className="mt-4"
        onPress={handleSubmit} 
        disabled={isLoading}
      >
        {isLoading ? <Loading /> : <Text>{submitText || 'Prześlij'}</Text>}
      </Button>
    </View>
  );
};

export default Form;
