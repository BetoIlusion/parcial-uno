<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Json;
use App\Models\DiagramaReporte;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Diagrama;
use ZipArchive;
use App\Models\UsuarioDiagrama;
use App\Models\User;
use Illuminate\Support\Facades\File;

class DiagramaController extends Controller
{
    // Métodos necesarios del controlador original
    public function procesarImagen(Request $request) {}

    public function create()
    {
        return view('diagramas.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:diagramas,nombre',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $diagrama = Diagrama::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'contenido' => json_encode(Diagrama::diagramaInicial(), JSON_PRETTY_PRINT)
        ]);

        DiagramaReporte::crear($user->id, $diagrama->id, Diagrama::diagramaInicial());
        UsuarioDiagrama::crearRelacion($user->id, $diagrama->id, 'creando diagrama', 'creador');
        return Redirect::route('diagramas.show', compact('diagrama'));
    }

    public function show(Diagrama $diagrama)
    {
        $diagramaId = $diagrama->id;
        $ultimoReporte = DiagramaReporte::query()
            ->where('diagrama_id', $diagrama->id)
            ->latest()->first();
        $jsonInicial = json_decode($ultimoReporte->contenido, true);
        return view('diagramas.uml', compact('jsonInicial', 'diagramaId'));
    }

    public function updateContenido(Request $request, Diagrama $diagrama)
    {
        $validated = $request->validate([
            'data' => 'required|array'
        ]);

        $diagrama->update([
            'contenido' => json_encode($validated['data'], JSON_PRETTY_PRINT)
        ]);

        return response()->json(['message' => 'Contenido actualizado']);
    }

    public function uml()
    {
        $modeloInicial = [
            'class' => 'go.GraphLinksModel',
            'nodeDataArray' => [],
            'linkDataArray' => []
        ];
        $jsonInicial = json_encode($modeloInicial);
        return view('diagramas.uml', ['jsonInicial' => $jsonInicial]);
    }

    public function diagramaReporte(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('Procesando solicitud de diagrama reporte', ['user_id' => $user->id]);

            $validated = $request->validate([
                'diagramData' => 'required',
                'diagramaId' => 'required|exists:diagramas,id'
            ]);

            $diagramaJson = $validated['diagramData'];
            $diagramaId = $validated['diagramaId'];
            Log::info('Contenido del diagrama JSON recibido', [
                'diagrama_id' => $diagramaId,
                'diagramData' => $diagramaJson
            ]);

            if (is_string($diagramaJson)) {
                $diagramaData = json_decode($diagramaJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error al decodificar JSON: ' . json_last_error_msg());
                }
            } else {
                $diagramaData = $diagramaJson;
            }

            $reporte = DiagramaReporte::crear($user->id, $diagramaId, $diagramaData);
            Log::info('Diagrama reporte creado exitosamente', ['reporte_id' => $reporte->id]);

            return response()->json([
                'message' => 'Diagrama guardado correctamente',
                'reporte_id' => $reporte->id
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Error de validación en diagrama reporte', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Datos inválidos',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al guardar diagrama reporte', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $diagrama = Diagrama::find($id);
        $diagrama->update(['estado' => false]);
        return Redirect::route('dashboard');
    }

     private function convertType($type)
    {
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                return 'Long';
            case 'string':
                return 'String';
            case 'double':
                return 'Double';
            case 'float':
                return 'Float';
            case 'boolean':
                return 'Boolean';
            case 'long':
                return 'Long';
            default:
                return $type;
        }
    }

    private function camelCase($str)
    {
        return lcfirst($str);
    }

    private function getSampleValue($type)
    {
        switch (strtolower($type)) {
            case 'string':
                return '"ejemplo_texto"';
            case 'int':
            case 'long':
            case 'integer':
                return '1';
            case 'double':
            case 'float':
                return '1.0';
            case 'boolean':
                return 'true';
            default:
                return 'null';
        }
    }

     public function exportSpringBoot($id)
    {
        try {
            Log::info('Iniciando exportación a Spring Boot', ['diagrama_id' => $id]);
            
            $ultimoReporte = DiagramaReporte::query()
                ->where('diagrama_id', $id)
                ->latest()
                ->firstOrFail();

            $diagramData = json_decode($ultimoReporte->contenido, true);
            if (!$diagramData) {
                throw new \Exception('Datos del diagrama inválidos');
            }

            $tempDir = storage_path('app/temp/' . uniqid('spring_', true));
            if (!mkdir($tempDir, 0755, true)) {
                throw new \Exception('Error al crear directorio temporal');
            }

            $this->createSpringBootStructure($tempDir, $diagramData);

            $zipFileName = 'spring-boot-project-' . date('Y-m-d-His') . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se puede crear el archivo ZIP');
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($tempDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            $this->removeDirectory($tempDir);

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error al exportar proyecto Spring Boot: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     private function createSpringBootStructure($baseDir, $diagramData)
    {
        // Crear estructura principal del proyecto
        $srcDir = $baseDir . '/src/main/java/com/example/demo';
        $resourcesDir = $baseDir . '/src/main/resources';
        
        // Crear directorios necesarios
        $dirs = [
            $srcDir . '/model',
            $srcDir . '/repository', 
            $srcDir . '/service',
            $srcDir . '/controller',
            $srcDir . '/dto',
            $resourcesDir
        ];
        
        foreach ($dirs as $dir) {
            if (!mkdir($dir, 0755, true)) {
                throw new \Exception("No se pudo crear directorio: $dir");
            }
        }

        // Generar archivos base del proyecto
        $this->generatePomXml($baseDir);
        $this->generateApplicationProperties($resourcesDir);
        $this->generateMainClass($srcDir);

        // Procesar cada clase del diagrama
    foreach ($diagramData['nodeDataArray'] as $classData) {
        $className = $classData['name'];
        $isInterface = isset($classData['stereotype']) && 
                      in_array(strtolower($classData['stereotype']), ['interfaz', 'interface']);
        
        // Solo generar código para clases (no interfaces)
        if (!$isInterface) {
            $this->generateEntityClass($srcDir . '/model', $className, $classData);
            $this->generateRepositoryInterface($srcDir . '/repository', $className);
            $this->generateServiceInterface($srcDir . '/service', $className);
            $this->generateServiceImpl($srcDir . '/service', $className);
            $this->generateController($srcDir . '/controller', $className, $classData); // Pasar classData
            
            if (!empty($classData['properties'])) {
                $this->generateDTO($srcDir . '/dto', $className, $classData);
            }
        }
    }

        // Generar archivo de ejemplos de requests HTTP
        $this->generateRequestsHttpFile($baseDir, $diagramData['nodeDataArray']);
    }

    // Los métodos de generación de archivos específicos se agregarán en el siguiente paso
    private function removeDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    private function generatePomXml($baseDir)
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>
<project xmlns="http://maven.apache.org/POM/4.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://maven.apache.org/POM/4.0.0 https://maven.apache.org/xsd/maven-4.0.0.xsd">
    <modelVersion>4.0.0</modelVersion>
    <parent>
        <groupId>org.springframework.boot</groupId>
        <artifactId>spring-boot-starter-parent</artifactId>
        <version>2.7.0</version>
    </parent>
    <groupId>com.example</groupId>
    <artifactId>demo</artifactId>
    <version>0.0.1-SNAPSHOT</version>
    <name>demo</name>
    <description>Proyecto generado desde diagrama UML</description>
    
    <properties>
        <java.version>11</java.version>
    </properties>
    
    <dependencies>
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-web</artifactId>
        </dependency>
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-data-jpa</artifactId>
        </dependency>
        <dependency>
            <groupId>com.h2database</groupId>
            <artifactId>h2</artifactId>
            <scope>runtime</scope>
        </dependency>
        <dependency>
            <groupId>org.projectlombok</groupId>
            <artifactId>lombok</artifactId>
            <optional>true</optional>
        </dependency>
    </dependencies>
    
    <build>
        <plugins>
            <plugin>
                <groupId>org.springframework.boot</groupId>
                <artifactId>spring-boot-maven-plugin</artifactId>
            </plugin>
        </plugins>
    </build>
</project>';
        
        file_put_contents($baseDir . '/pom.xml', $content);
    }


    private function generateApplicationProperties($resourcesDir)
    {
        $content = "spring.datasource.url=jdbc:h2:mem:testdb
spring.datasource.driverClassName=org.h2.Driver
spring.datasource.username=sa
spring.datasource.password=
spring.jpa.database-platform=org.hibernate.dialect.H2Dialect
spring.h2.console.enabled=true
spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=true";
        
        file_put_contents($resourcesDir . '/application.properties', $content);
    }

     private function generateMainClass($srcDir)
    {
        $content = "package com.example.demo;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class DemoApplication {
    public static void main(String[] args) {
        SpringApplication.run(DemoApplication.class, args);
    }
}";
        
        file_put_contents($srcDir . '/DemoApplication.java', $content);
    }

    private function generateEntityClass($dir, $className, $classData)
    {
        $properties = isset($classData['properties']) ? $classData['properties'] : [];
        $methods = isset($classData['methods']) ? $classData['methods'] : [];
        
        // Generar propiedades
        $propertiesCode = '';
        $hasId = false;
        
        foreach ($properties as $prop) {
            $type = $this->convertType($prop['type']);
            $name = $prop['name'];
            
            if (strtolower($name) === 'id') {
                $hasId = true;
                $propertiesCode .= "    @Id\n    @GeneratedValue(strategy = GenerationType.IDENTITY)\n";
            }
            
            // Usar private para todos los campos
            $propertiesCode .= "    private {$type} {$name};\n\n";
        }
        
        // Si no hay ID, agregarlo al principio
        if (!$hasId) {
            $propertiesCode = "    @Id\n    @GeneratedValue(strategy = GenerationType.IDENTITY)\n    private Long id;\n\n" . $propertiesCode;
        }

        // Generar métodos de la entidad
        $methodsCode = '';
        foreach ($methods as $method) {
            $methodName = $method['name'];
            $parameters = isset($method['parameters']) ? $method['parameters'] : [];
            
            $paramList = [];
            foreach ($parameters as $param) {
                $paramType = $this->convertType($param['type']);
                $paramList[] = "{$paramType} {$param['name']}";
            }
            $paramStr = implode(', ', $paramList);
            
            $methodsCode .= "    public void {$methodName}({$paramStr}) {\n        // TODO: Implementar {$methodName}\n    }\n\n";
        }

        $content = "package com.example.demo.model;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

@Entity
@Data
@NoArgsConstructor
@AllArgsConstructor
public class {$className} {
{$propertiesCode}{$methodsCode}}";
        
        file_put_contents($dir . "/{$className}.java", $content);
    }

    private function generateRepositoryInterface($dir, $className)
    {
        $content = "package com.example.demo.repository;

import com.example.demo.model.{$className};
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface {$className}Repository extends JpaRepository<{$className}, Long> {
}";
        
        file_put_contents($dir . "/{$className}Repository.java", $content);
    }

    
    private function generateServiceInterface($dir, $className)
    {
        $varName = $this->camelCase($className);
        
        $content = "package com.example.demo.service;

import com.example.demo.model.{$className};
import java.util.List;

public interface {$className}Service {
    {$className} save({$className} {$varName});
    List<{$className}> findAll();
    {$className} findById(Long id);
    void deleteById(Long id);
    List<String> getPropertyNames();
    List<String> getMethodNames();
}";
        
        file_put_contents($dir . "/{$className}Service.java", $content);
    }

    private function generateServiceImpl($dir, $className)
    {
        $varName = $this->camelCase($className);
        
        $content = "package com.example.demo.service;

import com.example.demo.model.{$className};
import com.example.demo.repository.{$className}Repository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import javax.persistence.EntityNotFoundException;
import java.util.List;
import java.util.Arrays;
import java.util.stream.Collectors;
import java.lang.reflect.Method;
import java.lang.reflect.Field;

@Service
public class {$className}ServiceImpl implements {$className}Service {
    
    @Autowired
    private {$className}Repository repository;

    @Override
    public {$className} save({$className} {$varName}) {
        if ({$varName} == null) {
            throw new IllegalArgumentException(\"{$className} cannot be null\");
        }
        return repository.save({$varName});
    }

    @Override
    public List<{$className}> findAll() {
        return repository.findAll();
    }

    @Override
    public {$className} findById(Long id) {
        if (id == null) {
            throw new IllegalArgumentException(\"Id cannot be null\");
        }
        return repository.findById(id)
            .orElseThrow(() -> new EntityNotFoundException(\"{$className} not found with id: \" + id));
    }

    @Override
    public void deleteById(Long id) {
        if (id == null) {
            throw new IllegalArgumentException(\"Id cannot be null\");
        }
        if (!repository.existsById(id)) {
            throw new EntityNotFoundException(\"{$className} not found with id: \" + id);
        }
        repository.deleteById(id);
    }

    @Override
    public List<String> getPropertyNames() {
        return Arrays.stream({$className}.class.getDeclaredFields())
            .map(Field::getName)
            .filter(name -> !name.equals(\"serialVersionUID\"))
            .collect(Collectors.toList());
    }

    @Override
    public List<String> getMethodNames() {
        return Arrays.stream({$className}.class.getDeclaredMethods())
            .map(Method::getName)
            .filter(name -> !name.startsWith(\"$\") && !name.equals(\"getClass\") && !name.equals(\"wait\") && 
                   !name.equals(\"equals\") && !name.equals(\"hashCode\") && !name.equals(\"notify\") && 
                   !name.equals(\"notifyAll\") && !name.equals(\"toString\"))
            .collect(Collectors.toList());
    }
}";
        
        file_put_contents($dir . "/{$className}ServiceImpl.java", $content);
    }

     private function generateController($dir, $className, $classData)
{
    $varName = $this->camelCase($className);
    $pluralVarName = $varName . 's';
    
    // Generar código de actualización dinámico basado en las propiedades reales
    $updateLogic = '';
    if (isset($classData['properties'])) {
        foreach ($classData['properties'] as $prop) {
            $propName = $prop['name'];
            if (strtolower($propName) !== 'id') {
                $updateLogic .= "            if ({$varName}.get" . ucfirst($propName) . "() != null) {\n";
                $updateLogic .= "                existing.set" . ucfirst($propName) . "({$varName}.get" . ucfirst($propName) . "());\n";
                $updateLogic .= "            }\n";
            }
        }
    }
    
    // Si no hay propiedades específicas, usar una actualización genérica
    if (empty($updateLogic)) {
        $updateLogic = "            // Usar actualización directa ya que no hay propiedades específicas\n";
        $updateLogic .= "            {$varName}.setId(existing.getId());\n";
    }

    $content = "package com.example.demo.controller;

import com.example.demo.model.{$className};
import com.example.demo.service.{$className}Service;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import javax.persistence.EntityNotFoundException;
import java.util.List;
import java.util.HashMap;
import java.util.Map;

@RestController
@RequestMapping(\"/api/{$pluralVarName}\")
@CrossOrigin(origins = \"*\")
public class {$className}Controller {
    
    @Autowired
    private {$className}Service service;

    @PostMapping
    public ResponseEntity<?> create(@RequestBody {$className} {$varName}) {
        try {
            {$className} saved = service.save({$varName});
            return new ResponseEntity<>(saved, HttpStatus.CREATED);
        } catch (IllegalArgumentException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.BAD_REQUEST);
        } catch (Exception e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @GetMapping
    public ResponseEntity<List<{$className}>> getAll() {
        return ResponseEntity.ok(service.findAll());
    }

    @GetMapping(\"/{id}\")
    public ResponseEntity<?> getById(@PathVariable Long id) {
        try {
            {$className} entity = service.findById(id);
            return ResponseEntity.ok(entity);
        } catch (EntityNotFoundException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.NOT_FOUND);
        } catch (IllegalArgumentException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.BAD_REQUEST);
        }
    }

    @GetMapping(\"/{id}/properties\")
    public ResponseEntity<?> getProperties(@PathVariable Long id) {
        try {
            service.findById(id); // Verificar existencia
            return ResponseEntity.ok(service.getPropertyNames());
        } catch (EntityNotFoundException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.NOT_FOUND);
        }
    }

    @GetMapping(\"/{id}/methods\")
    public ResponseEntity<?> getMethods(@PathVariable Long id) {
        try {
            service.findById(id); // Verificar existencia
            return ResponseEntity.ok(service.getMethodNames());
        } catch (EntityNotFoundException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.NOT_FOUND);
        }
    }

    @PutMapping(\"/{id}\")
    public ResponseEntity<?> update(@PathVariable Long id, @RequestBody {$className} {$varName}) {
        try {
            {$className} existing = service.findById(id);
            
            // Actualización dinámica basada en propiedades existentes
{$updateLogic}
            
            {$className} updated = service.save(existing);
            return ResponseEntity.ok(updated);
        } catch (EntityNotFoundException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.NOT_FOUND);
        } catch (IllegalArgumentException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.BAD_REQUEST);
        }
    }

    @DeleteMapping(\"/{id}\")
    public ResponseEntity<?> delete(@PathVariable Long id) {
        try {
            service.deleteById(id);
            return ResponseEntity.ok().build();
        } catch (EntityNotFoundException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.NOT_FOUND);
        } catch (IllegalArgumentException e) {
            return new ResponseEntity<>(createErrorResponse(e), HttpStatus.BAD_REQUEST);
        }
    }

    private Map<String, String> createErrorResponse(Exception e) {
        Map<String, String> response = new HashMap<>();
        response.put(\"error\", e.getMessage());
        return response;
    }
}";
        
    file_put_contents($dir . "/{$className}Controller.java", $content);
}

     private function generateDTO($dir, $className, $classData)
    {
        $properties = isset($classData['properties']) ? $classData['properties'] : [];
        
        $propertiesCode = '';
        foreach ($properties as $prop) {
            $type = $this->convertType($prop['type']);
            $name = $prop['name'];
            $propertiesCode .= "    private {$type} {$name};\n";
        }

        $content = "package com.example.demo.dto;

import lombok.Data;

@Data
public class {$className}DTO {
{$propertiesCode}}";
        
        file_put_contents($dir . "/{$className}DTO.java", $content);
    }

    private function generateRequestsHttpFile($baseDir, $classes)
    {
        $content = "### Ejemplos de Requests para API Spring Boot\n\n";
        $baseUrl = "http://localhost:8080/api";

        foreach ($classes as $class) {
            $isInterface = isset($class['stereotype']) && 
                          in_array(strtolower($class['stereotype']), ['interfaz', 'interface']);
            
            if (!$isInterface) {
                $className = $class['name'];
                $pluralVarName = $this->camelCase($className) . 's';
                
                $content .= "### ==================================\n";
                $content .= "### {$className} Endpoints\n";
                $content .= "### ==================================\n\n";
                
                $content .= "### Crear nuevo {$className}\n";
                $content .= "POST {$baseUrl}/{$pluralVarName}\n";
                $content .= "Content-Type: application/json\n\n";
                
                $sampleJson = $this->generateSampleJson($class);
                $content .= $sampleJson . "\n\n";

                $content .= "### Obtener todos los {$className}s\n";
                $content .= "GET {$baseUrl}/{$pluralVarName}\n\n";

                $content .= "### Obtener {$className} por ID\n";
                $content .= "GET {$baseUrl}/{$pluralVarName}/1\n\n";

                $content .= "### Obtener propiedades de {$className}\n";
                $content .= "GET {$baseUrl}/{$pluralVarName}/1/properties\n\n";

                $content .= "### Obtener métodos de {$className}\n";
                $content .= "GET {$baseUrl}/{$pluralVarName}/1/methods\n\n";

                $content .= "### Actualizar {$className}\n";
                $content .= "PUT {$baseUrl}/{$pluralVarName}/1\n";
                $content .= "Content-Type: application/json\n\n";
                $content .= $sampleJson . "\n\n";

                $content .= "### Eliminar {$className}\n";
                $content .= "DELETE {$baseUrl}/{$pluralVarName}/1\n\n";
            }
        }

        file_put_contents($baseDir . '/requests.http', $content);
    }

    private function generateSampleJson($classData)
    {
        $json = "{\n";
        if (isset($classData['properties'])) {
            foreach ($classData['properties'] as $index => $prop) {
                $name = $prop['name'];
                $type = $prop['type'];
                $sampleValue = $this->getSampleValue($type);
                $json .= "    \"$name\": $sampleValue";
                if ($index < count($classData['properties']) - 1) {
                    $json .= ",";
                }
                $json .= "\n";
            }
        }
        $json .= "}";
        return $json;
    }
}
