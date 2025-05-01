import './global.css';

import { Provider } from 'react-redux';
import { PersistGate } from 'redux-persist/integration/react';
import { store, persistor } from './src/store';
import Navigation from './src/navigation';
import LoadingScreen from './src/screens/loading';

/**
 * Main application component that sets up the Redux store and navigation.
 * 
 * This component serves as the entry point of the application and wraps the entire app
 * with necessary providers:
 * - Redux Provider for state management
 * - PersistGate to ensure persisted state is rehydrated before rendering the app
 * - Navigation component which handles routing between different screens
 * 
 */
export default function App() {
  return (
      <Provider store={store}>
        <PersistGate loading={<LoadingScreen />} persistor={persistor}>
          <Navigation />
        </PersistGate>
      </Provider>
  );
}
