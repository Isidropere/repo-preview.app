import com.android.build.gradle.BaseExtension

val flutterMap = mapOf(
    "compileSdkVersion" to 35,
    "minSdkVersion" to 26,
    "targetSdkVersion" to 35,
    "ndkVersion" to "23.1.7779620"
)
extra.set("flutter", flutterMap)

allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}
subprojects {
    project.evaluationDependsOn(":app")
}

subprojects {
    plugins.withId("com.android.application") {
        val androidComponents = extensions.getByType<com.android.build.api.variant.ApplicationAndroidComponentsExtension>()
        androidComponents.finalizeDsl { ext ->
            ext.compileSdk = 35
            ext.buildToolsVersion = "36.0.0"
        }
    }
    plugins.withId("com.android.library") {
        val androidComponents = extensions.getByType<com.android.build.api.variant.LibraryAndroidComponentsExtension>()
        androidComponents.finalizeDsl { ext ->
            ext.compileSdk = 35
            ext.buildToolsVersion = "36.0.0"
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
