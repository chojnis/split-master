import { SafeAreaView } from 'react-native';

export const Container = ({ children, className }: { children: React.ReactNode, className?: string }) => {
  return (
    <SafeAreaView 
      className={`flex flex-1 m-6 ${className || ''}`}
    >
        {children}
    </SafeAreaView>
  );
};
