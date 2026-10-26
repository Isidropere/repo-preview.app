# Flutter specific rules
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.util.** { *; }
-keep class io.flutter.view.** { *; }
-keep class io.flutter.** { *; }
-keep class io.flutter.plugins.** { *; }

# Preserve Flutter engine entrypoints
-keepclassmembers class * {
    @androidx.annotation.Keep *;
}

# Preserve JNI annotations and reflective access
-keepattributes *Annotation*,Signature,InnerClasses,EnclosingMethod,Exceptions

# Suppress known harmless warnings
-dontwarn java.lang.invoke.**
-dontwarn **Compat**
-dontwarn javax.annotation.**
-dontwarn org.checkerframework.**
-dontwarn sun.misc.Unsafe
-dontwarn com.google.android.play.core.**

# Google Sign In / Play Services
-keep class com.google.android.gms.auth.api.signin.** { *; }
-keep class com.google.android.gms.common.** { *; }

# Flutter Secure Storage
-keep class com.it_nomads.fluttersecurestorage.** { *; }

# Geolocator / Permissions
-keep class com.baseflow.geolocator.** { *; }
-keep class com.baseflow.permissionhandler.** { *; }

# Image Picker
-keep class io.flutter.plugins.imagepicker.** { *; }

# Path Provider / Shared Preferences / URL Launcher
-keep class io.flutter.plugins.pathprovider.** { *; }
-keep class io.flutter.plugins.sharedpreferences.** { *; }
-keep class io.flutter.plugins.urllauncher.** { *; }
