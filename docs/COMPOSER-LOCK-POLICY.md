# Composer Lock Policy

## Por qué NO versionamos composer.lock en esta librería

Este es un **paquete de librería** (library), no una aplicación. Hay diferencias fundamentales en cómo se deben gestionar las dependencias:

## 📦 Librería vs Aplicación

### Aplicación (versionar composer.lock ✅)
- **Propósito**: Código desplegado directamente
- **Lock file**: Se versiona para garantizar instalaciones idénticas
- **CI/CD**: Usa `composer install` (instalar dependencias exactas)
- **Ejemplo**: WordPress, Symfony app, Laravel app

### Librería (NO versionar composer.lock ❌)
- **Propósito**: Código consumido por otras aplicaciones
- **Lock file**: NO se versiona, cada proyecto resuelve sus dependencias
- **CI/CD**: Usa `composer update` (resolver dependencias para cada PHP)
- **Ejemplo**: Este paquete, Guzzle, Monolog

## 🎯 Por qué esto es importante

### Problema con PHP 7.4

Cuando generamos `composer.lock` en PHP 8.3:
```json
{
  "doctrine/instantiator": "2.0.0"  // Requiere PHP ^8.1
}
```

Cuando CI ejecuta con PHP 7.4:
```bash
composer install  # ❌ ERROR: doctrine/instantiator 2.0.0 requiere PHP ^8.1
```

### Solución: composer update

Cuando CI ejecuta con PHP 7.4:
```bash
composer update  # ✅ OK: Resuelve doctrine/instantiator 1.x (compatible PHP 7.4)
```

## 🔄 Cómo funciona en CI/CD

Nuestro workflow `.github/workflows/phpunit.yml`:

```yaml
strategy:
  matrix:
    php-version: ['8.3', '8.2', '8.1', '7.4']

steps:
  - name: Install Composer dependencies
    run: composer update --prefer-dist --no-progress --no-interaction
```

**Resultado**:
- PHP 7.4 → Instala `doctrine/instantiator 1.x`
- PHP 8.1 → Instala `doctrine/instantiator 1.x` o `2.x`
- PHP 8.3 → Instala `doctrine/instantiator 2.x`

Cada versión de PHP obtiene dependencias compatibles.

## 📚 Documentación Oficial

### Composer Documentation
> "For libraries, it is not necessary to commit the lock file" 
> 
> Source: https://getcomposer.org/doc/02-libraries.md#lock-file

### Razones técnicas:

1. **Flexibilidad de versiones**: Los consumidores de la librería deben poder resolver sus propias versiones de dependencias
2. **Compatibilidad multi-PHP**: Cada versión de PHP puede necesitar diferentes versiones de dependencias
3. **Evitar conflictos**: Si versionas el lock, otros proyectos pueden tener conflictos con sus propias dependencias

## ✅ Best Practices para esta Librería

### En desarrollo local:
```bash
composer install  # Usa composer.lock si existe localmente (conveniencia)
composer update   # Actualizar dependencias cuando sea necesario
```

### En .gitignore:
```gitignore
# No versionar composer.lock para librerías
/vendor/
```

**Nota**: `composer.lock` NO está en `.gitignore` porque:
- Es útil localmente para desarrollo consistente
- Git lo ignora automáticamente si no lo añades con `git add`
- CI siempre usa `composer update` (no necesita lock file)

### En CI/CD:
```bash
composer update  # Siempre resolver dependencias frescas
```

### Al publicar en Packagist:
- Solo se publica `composer.json`
- Los consumidores resolverán dependencias según sus restricciones

## 🚫 Qué NO hacer

❌ **NO ejecutar `git add composer.lock`**
```bash
# Esto causa el problema de PHP 7.4
git add composer.lock
git commit -m "Update dependencies"
```

❌ **NO usar `composer install` en CI para librerías**
```yaml
# Esto fallará con múltiples versiones de PHP
- run: composer install
```

## ✅ Qué SÍ hacer

✅ **Eliminar composer.lock del repositorio**
```bash
git rm composer.lock
git commit -m "Remove composer.lock (library best practice)"
```

✅ **Usar `composer update` en CI**
```yaml
- run: composer update --prefer-dist --no-progress --no-interaction
```

## 📊 Impacto en el Proyecto

### Antes (con composer.lock versionado):
- ❌ CI falla en PHP 7.4
- ❌ Dependencias fijas para todas las versiones de PHP
- ❌ No sigue best practices de librerías

### Después (sin composer.lock versionado):
- ✅ CI pasa en PHP 7.4, 8.1, 8.2, 8.3
- ✅ Cada PHP resuelve dependencias compatibles
- ✅ Sigue best practices oficiales de Composer

## 🔍 Verificación

Para verificar que tu setup es correcto:

```bash
# 1. composer.lock no debe estar en el repositorio
git ls-files | grep composer.lock
# Resultado esperado: (ninguna salida)

# 2. CI debe usar composer update
grep "composer update" .github/workflows/*.yml
# Resultado esperado: encontrar "composer update" en workflows

# 3. Tests deben pasar localmente
composer update && composer test
# Resultado esperado: OK (14 tests, 16 assertions)
```

## 📖 Referencias

- [Composer Libraries Documentation](https://getcomposer.org/doc/02-libraries.md)
- [Composer Lock File](https://getcomposer.org/doc/01-basic-usage.md#commit-your-composer-lock-file-to-version-control)
- [Packagist Best Practices](https://packagist.org/about)

## ❓ FAQ

**P: ¿Por qué tengo composer.lock localmente?**
R: Es normal. Composer lo genera automáticamente y es útil para desarrollo local. Simplemente no lo versiones con git.

**P: ¿Debo ejecutar composer update antes de cada commit?**
R: No necesariamente. Solo cuando actualices dependencias. Los tests se encargan de verificar compatibilidad.

**P: ¿Y si necesito reproducir un bug con versiones exactas?**
R: Puedes compartir tu `composer.lock` localmente fuera del repositorio, o especificar versiones exactas temporalmente en `composer.json`.

---

**Última actualización**: Diciembre 2024










