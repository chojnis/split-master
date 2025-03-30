import { useEffect } from 'react';
import { TouchableOpacity, View, StyleSheet } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Option
} from '~/components/ui/select';
import { Text } from '~/components/ui/text';
import RNPickerSelect from 'react-native-picker-select';
import { MultiSelect } from 'react-native-element-dropdown';
import AntDesign from '@expo/vector-icons/AntDesign';

// export type SelectOption = Option;
export type SelectOption = {
    label: string;
    value: string;
};

type SelectProps = {
    value?: string | string[];
    onChangeValue: (value: string | string[]) => void;
    selectOptions: SelectOption[];
    defaultValue?: SelectOption | SelectOption[];
    className?: string;
    multiple?: boolean;
}

const findOptionByValue = (options: SelectOption[], value?: string) => {
    return options.find(option => option.value == value);
}

const findOptionsByValue = (options: SelectOption[], values?: string[]) => {
    return options.filter(option => values?.includes(option.value));
}

const SelectField = ({
    value,
    onChangeValue, 
    className,
    selectOptions,
    defaultValue,
    multiple
}: SelectProps) => {
    const selectedOption = multiple 
        ? findOptionsByValue(selectOptions, Array.isArray(value) ? value : [])
        : findOptionByValue(selectOptions, typeof value === 'string' ? value : undefined);
    const singleSelectedOption = multiple ? undefined : selectedOption as SelectOption | undefined;
    const singleDefaultValue = multiple ? undefined : defaultValue as SelectOption | undefined;

    const renderMultiSelectItem = (item: SelectOption) => {
        return (
          <View className="p-4 flex-row justify-between items-center">
            <Text className="text-sm">{item.label}</Text>
          </View>
        );
    };

    return (
        <View className={`${className || ''}`}>
            {!multiple ? (
                <Select 
                    value={singleSelectedOption}
                    onValueChange={(option) => {
                        if(option){
                            onChangeValue(option.value);
                        }
                    }}
                >
                    <SelectTrigger className={`w-full ${className}`}>
                        <View className="flex-row items-center justify-between">
                            <Text className="text-base">{singleSelectedOption?.label || singleDefaultValue?.label}</Text>
                        </View>
                    </SelectTrigger>
                    <SelectContent className="shadow-lg rounded-lg">
                        {selectOptions.map((option) => (
                            <SelectItem key={option.value} value={option.value} label={option.label}>
                                <Text className="text-base">{option.label}</Text>
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ) : (
                <View className="flex flex-1 mb-8">
                    <MultiSelect
                        style={{
                            height: 45,
                            // backgroundColor: 'white',
                            borderRadius: 6,
                            padding: 12,
                            borderWidth: 1,
                            borderColor: '#E7E5E4'
                        }}
                        containerStyle={{
                            // backgroundColor: 'white',
                            borderRadius: 8,
                            marginTop: 8,
                            shadowColor: '#000',
                            shadowOffset: { width: 0, height: 2 },
                            shadowOpacity: 0.15,
                            shadowRadius: 3,
                            elevation: 5,
                        }}
                        itemContainerStyle={{
                            padding: 8,
                        }}
                        placeholderStyle={{
                            color: '#9ca3af', // Placeholder color
                        }}
                        selectedTextStyle={{
                            fontSize: 16,
                        }}
                        data={selectOptions}
                        onChange={(values) => {
                            onChangeValue(values);
                        }}
                        value={Array.isArray(value) ? value : []}
                        renderItem={renderMultiSelectItem}
                        renderSelectedItem={(item, unSelect) => <></>} 
                        labelField="label" 
                        valueField="value"
                        placeholder={Array.isArray(value) && value.length > 0 && `Wybrano: ${value.length}` || "Wybierz"}
                    />
                </View>
            )}
        </View>
    );
}

export default SelectField;