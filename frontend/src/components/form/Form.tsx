import { useEffect, useState } from 'react';
import { View } from 'react-native';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import FormField, { FormFieldValue } from '~/components/form/FormField';
import Loading from '~/components/Loading';
import { ApiError } from '~/api/types';
import { SerializedError } from '@reduxjs/toolkit';
import ErrorText from '~/components/ErrorText';
import { SelectOption } from '~/components/form/SelectField';

export type FormFieldType = {
  label: string;
  name: string;
  type: 'text' | 'number' | 'textarea' | 'password' | 'select';
  value?: FormFieldValue;
  required?: boolean;
  width?: number;
  placeholder?: string;
  selectOptions?: SelectOption[];
  defaultSelectValue?: SelectOption | SelectOption[];
  multiple?: boolean;
}

type FormProps = {
  fields: FormFieldType[];
  onSubmit: (data: { [key: string]: FormFieldValue }) => void;
  onChange?: (data: { [key: string]: FormFieldValue }) => void;
  isLoading?: boolean;
  error?: ApiError | SerializedError;
  submitText?: string;
  submitClassName?: string;
}

export type FormDataType = {
  [key: string]: FormFieldValue;
}

type ErrorType = {
  [key: string]: string | undefined;
}

const Form = ({ fields, onSubmit, onChange, error, isLoading, submitText, submitClassName }: FormProps) => {
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

  useEffect(() => {

    const initialData: FormDataType = {};
    fields.forEach(field => {
      // if (field.type === 'select' && field.defaultSelectValue !== undefined && formData[field.name] === undefined) {
      //   initialData[field.name] = Array.isArray(field.defaultSelectValue)
      //     ? field.defaultSelectValue.map(option => option.value)
      //     : field.defaultSelectValue.value;
      // }

      if (
        field.value
        && field.value !== undefined
        && formData[field.name] === undefined
      ) {
        initialData[field.name] = field.value;
      }
    });

    setFormData(prev => ({ ...prev, ...initialData }));
    onChange && onChange({ ...formData, ...initialData });
  }, []);

  const handleChange = (name: string, value: FormFieldValue) => {
    setFormData(prev => ({ ...prev, [name]: value }));
    setErrors(prev => ({...prev, [name]: undefined}));
    if (onChange) {
      onChange({ ...formData, [name]: value });
    }
  };

  const handleSubmit = () => {
    let isValid = true;
    const newErrors: ErrorType = {};
    for (const field of fields) {
      if ((!formData[field.name] || 
          (Array.isArray(formData[field.name]) && (formData[field.name] as string[]).length === 0)) && 
          field.required) {
        newErrors[field.name] = 'To pole jest wymagane';
        isValid = false;
      }
    }
    if (!isValid) {
      setErrors(newErrors);
      return;
    }

    console.log('Form data:', formData);
    onSubmit(formData);
  };

  const groupFieldsIntoRows = () => {
    const rows: FormFieldType[][] = [];
    let currentRow: FormFieldType[] = [];
    let currentRowWidth = 0;

    fields.forEach((field, index) => {
      const fieldWidth = field.width || 100;
      
      if (currentRowWidth + fieldWidth > 100) {
        rows.push(currentRow);
        currentRow = [field];
        currentRowWidth = fieldWidth;
      } else {
        currentRow.push(field);
        currentRowWidth += fieldWidth;
      }

      if (index === fields.length - 1) {
        rows.push(currentRow);
      }
    });

    return rows;
  };

  return (
    <View>
      {generalError && (
        <ErrorText>{generalError}</ErrorText>
      )}
      {groupFieldsIntoRows().map((row, rowIndex) => (
        <View key={`row-${rowIndex}`} className="flex-row mb-4">
          {row.map((field) => (
            <View 
              key={field.name} 
              style={{
                width: typeof field.width === 'number' 
                  ? `${field.width}%` 
                  : field.width || '100%',
                paddingRight: 8,
              }}
            >
              <FormField
                field={field}
                value={formData[field.name]}
                onChange={handleChange}
                error={errors[field.name]}
              />
            </View>
          ))}
        </View>
      ))}
      <Button 
        className={`mt-4 ${submitClassName || ''}`}
        onPress={handleSubmit} 
        disabled={isLoading}
      >
        {isLoading ? <Loading /> : <Text>{submitText || 'Prześlij'}</Text>}
      </Button>
    </View>
  );
};

export default Form;
