/**
 * ============================================================
 * TabNavigator.tsx  Navegación principal de la app
 * ============================================================
 * Define la estructura de navegación completa:
 *
 *   Tab Bar (bottom tabs):
 *     Inicio    HomeStack   (Home  ProductDetail  Negotiation)
 *     Carrito   CartStack   (Cart  Checkout  ProductDetail)
 *     Mensajes  MsgStack    (ConversationList  Chat)
 *     Perfil    ProfileStack (Profile)
 *
 * Cada tab tiene su propio Stack Navigator para que la
 * navegación interna no afecte a los otros tabs.
 * ============================================================
 */

import React from 'react';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors} from '../config/theme';

// Importar todas las pantallas
import HomeScreen from '../../features/home/screens/HomeScreen';
import CartScreen from '../../features/cart/screens/CartScreen';
import ConversationListScreen from '../../features/messages/screens/ConversationListScreen';
import ChatScreen from '../../features/messages/screens/ChatScreen';
import ProfileScreen from '../../features/profile/screens/ProfileScreen';
import ProductDetailScreen from '../../features/products/screens/ProductDetailScreen';
import NegotiationScreen from '../../features/negotiations/screens/NegotiationScreen';
import CheckoutScreen from '../../features/checkout/screens/CheckoutScreen';

// Crear instancias de navegadores
const Tab = createBottomTabNavigator();
const HomeStack = createNativeStackNavigator();
const CartStack = createNativeStackNavigator();
const MsgStack = createNativeStackNavigator();
const ProfileStack = createNativeStackNavigator();

/**
 * Stack del tab Inicio.
 * Flujo: Home  ProductDetail  Negotiation
 * ProductDetail y Negotiation no tienen header nativo (headerShown: false)
 * porque cada pantalla tiene su propio header personalizado.
 */
const HomeStackScreen = () => (
  <HomeStack.Navigator screenOptions={{headerShown: false}}>
    <HomeStack.Screen name="HomeMain" component={HomeScreen} />
    <HomeStack.Screen name="ProductDetail" component={ProductDetailScreen} />
    <HomeStack.Screen name="Negotiation" component={NegotiationScreen} />
  </HomeStack.Navigator>
);

/**
 * Stack del tab Carrito.
 * Flujo: Cart  Checkout
 * También incluye ProductDetail para navegar desde el carrito al detalle.
 */
const CartStackScreen = () => (
  <CartStack.Navigator screenOptions={{headerShown: false}}>
    <CartStack.Screen name="CartMain" component={CartScreen} />
    <CartStack.Screen name="Checkout" component={CheckoutScreen} />
    <CartStack.Screen name="ProductDetail" component={ProductDetailScreen} />
  </CartStack.Navigator>
);

/**
 * Stack del tab Mensajes.
 * Flujo: ConversationList  Chat
 * Chat recibe userId, nombre y foto como params de navegación.
 */
const MsgStackScreen = () => (
  <MsgStack.Navigator screenOptions={{headerShown: false}}>
    <MsgStack.Screen name="ConversationList" component={ConversationListScreen} />
    <MsgStack.Screen name="Chat" component={ChatScreen} />
  </MsgStack.Navigator>
);

/**
 * Stack del tab Perfil.
 * Solo tiene una pantalla por ahora.
 * CODIFICABLE: agregar pantallas de editar perfil, mis productos, etc.
 */
const ProfileStackScreen = () => (
  <ProfileStack.Navigator screenOptions={{headerShown: false}}>
    <ProfileStack.Screen name="ProfileMain" component={ProfileScreen} />
  </ProfileStack.Navigator>
);

/**
 * Navegador principal con tabs en la parte inferior.
 *
 * tabBarIcon: asigna el ícono de Ionicons según el nombre del tab.
 * CONFIGURABLE: cambiar iconos, colores y labels en screenOptions.
 */
const TabNavigator = () => {
  return (
    <Tab.Navigator
      screenOptions={({route}) => ({
        // Seleccionar ícono según el tab activo
        tabBarIcon: ({color, size}) => {
          let iconName = 'home-outline';
          if (route.name === 'Inicio')   iconName = 'home-outline';
          else if (route.name === 'Carrito')  iconName = 'cart-outline';
          else if (route.name === 'Mensajes') iconName = 'chatbubbles-outline';
          else if (route.name === 'Perfil')   iconName = 'person-outline';
          return <Icon name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: colors.primary,     // CONFIGURABLE: color tab activo
        tabBarInactiveTintColor: colors.textSecondary, // CONFIGURABLE: color tab inactivo
        headerShown: false,
      })}>
      <Tab.Screen name="Inicio"   component={HomeStackScreen} />
      <Tab.Screen name="Carrito"  component={CartStackScreen} />
      <Tab.Screen name="Mensajes" component={MsgStackScreen} />
      <Tab.Screen name="Perfil"   component={ProfileStackScreen} />
    </Tab.Navigator>
  );
};

export default TabNavigator;