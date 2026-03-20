import React, {useEffect} from 'react';
import {StatusBar, useColorScheme} from 'react-native';
import {GestureHandlerRootView} from 'react-native-gesture-handler';
import {SafeAreaProvider} from 'react-native-safe-area-context';
import AppNavigator from './src/core/navigation/AppNavigator';
import {useAuthStore} from './src/core/store/authStore';

function App() {
  const isDarkMode = useColorScheme() === 'dark';
  const loadToken = useAuthStore(s => s.loadToken);

  useEffect(() => {
    loadToken();
  }, []);

  return (
    <GestureHandlerRootView style={{flex: 1}}>
      <SafeAreaProvider>
        <StatusBar barStyle={isDarkMode ? 'light-content' : 'dark-content'} />
        <AppNavigator />
      </SafeAreaProvider>
    </GestureHandlerRootView>
  );
}

export default App;
