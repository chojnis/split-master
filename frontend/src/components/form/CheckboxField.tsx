import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView } from 'react-native';
import Checkbox from 'expo-checkbox';

interface Option {
    label: string;
    value: string;
}

interface CheckboxFieldProps {
    // label: string;
    options: Option[];
    disabled?: boolean;
    values: string[];
    onChange: (values: string[]) => void;
    // error?: string;
}

const CheckboxField: React.FC<CheckboxFieldProps> = ({
    // label,
    options,
    disabled,
    values,
    onChange,
    // error,
}) => {
    const handleToggle = (value: string) => {
        const newValues = values.includes(value)
            ? values.filter(v => v !== value)
            : [...values, value];
        
        onChange(newValues);
    };

    return (
        <ScrollView className="p-2 h-36" showsVerticalScrollIndicator={true}>
            {options.map((option, index) => (
                <TouchableOpacity
                key={option.value}
                onPress={() => handleToggle(option.value)}
                >
                <View className={`flex flex-row items-center p-3 ${values.includes(option.value) ? 'bg-green-100' : 'bg-stone-100'} rounded-md ${index !== options.length - 1 ? 'mb-2' : 'mb-4'}`}>
                    <Checkbox
                        value={values.includes(option.value)}
                        onValueChange={() => handleToggle(option.value)}
                        style={styles.checkbox}
                        color={values.includes(option.value) ? '#50C878' : undefined}
                        disabled={disabled}
                    />
                    <Text style={styles.checkboxLabel}>{option.label}</Text>
                </View>
                </TouchableOpacity>
            ))}
        </ScrollView>
    );
};

const styles = StyleSheet.create({
    container: {
        marginBottom: 16,
    },
    label: {
        fontSize: 16,
        fontWeight: '500',
        marginBottom: 8,
    },
    optionsContainer: {
        marginTop: 4,
    },
    checkboxContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 8,
    },
    checkbox: {
        marginRight: 8,
    },
    checkboxLabel: {
        fontSize: 14,
    },
    errorText: {
        color: 'red',
        fontSize: 12,
        marginTop: 4,
    },
});

export default CheckboxField;