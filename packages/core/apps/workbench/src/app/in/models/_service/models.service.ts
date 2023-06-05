import { Injectable } from '@angular/core';
import { ApiService } from 'sb-shared-lib';


@Injectable({
    providedIn: 'root'
})
export class WorkbenchService {

    constructor(private api: ApiService) { }

    /**
     * // TODO
     *
     * @param new_package - The name of the new package.
     */
    public createPackage(new_package: string) {
        // TODO
        console.warn("New package name: ", new_package);
    }

    /**
     * // TODO
     *
     * @param old_node - The name of the old package.
     * @param new_node - The name of the new package.
     */
    public updatePackage(old_node: string, new_node: string) {
        // TODO
        console.warn("Update name package, old name: ", old_node, ", new name: ", new_node);
    }

    /**
     * // TODO
     *
     * @param eq_package - name of the package to delete.
     */
    public deletePackage(eq_package: string) {
        // TODO
        console.warn("Package deleted: ", eq_package);
    }

    /**
     * // TODO
     *
     * @param eq_package - The name of package for the new class.
     * @param new_class - The name of the new class.
     */
    public createClass(eq_package: string, new_class: string) {
        // TODO
        console.warn("New class name: ", new_class);
    }

    /**
     * // TODO
     *
     * @param eq_package - The name of the package of the class.
     * @param old_node - The name of the old class.
     * @param new_node - The name of the new class.
     */
    public updateClass(eq_package: string, old_node: string, new_node: string) {
        // TODO
        console.warn("Update name package, old name: ", old_node, ", new name: ", new_node);
    }

    /**
     * // TODO
     *
     * @param eq_package - The name of the package for eq_class.
     * @param eq_class - The name of the class.
     */
    public deleteClass(eq_package: string, eq_class: string) {
        // TODO
        console.warn("Package deleted: ", eq_class);
    }

    /**
     * // TODO
     *
     * @param eq_package - The name of the package for eq_class.
     * @param eq_class - The name of the class for new_field.
     * @param new_field - The new name of the field.
     */
    public createField(eq_package: string, eq_class: string, new_field: string) {
        // TODO
        console.warn("New field name: ", new_field);
    }

    /**
     * // TODO
     *
     * @param eq_package - The name of the package for eq_class.
     * @param eq_class - The name of the class for new_field.
     * @param old_node - The old name of the field.
     * @param new_node - The new name of the field.
     */
    public updateField(eq_package: string, eq_class: string, old_node: string, new_node: string) {
        // TODO
        console.warn("Update name field, old name: ", old_node, ", new name: ", new_node);
    }

    /**
     * // TODO
     *
     * @param eq_package - The name of the package for eq_class.
     * @param eq_class - The name of the class for new_field.
     * @param field - The name of the field.
     */
    public deleteField(eq_package: string, eq_class: string, field: string) {
        // TODO
        console.warn("Field deleted: ", field);
    }

    /**
     * Return all the packages available.
     *
     * @returns A array with all packages
     */
    public async getPackages() {
        try {
            return await this.api.fetch('?get=config_packages');
        }
        catch (response: any) {
            console.warn('fetch package error', response);
        }
    }

    /**
     * Return all the classes foreach package
     *
     * @returns A JSON with package as key and classes as value.
     */
    public async getClasses() {
        try {
            return await this.api.fetch('?get=core_config_classes');
        }
        catch (response: any) {
            console.warn('fetch class error', response);
        }
    }

    /**
     * Return all the available types and foreach type, the properties and foreach property, the type.
     *
     * @returns A JSON of each type with key-values.
     */
    public async getTypes() {
        try {
            return await this.api.fetch('?get=config_types');
        }
        catch (response: any) {
            console.warn('fetch types error', response);
        }
    }

    /**
     * Return the schema of the specified entity.
     *
     * @param entity - The namespace of the schema.
     * @returns A JSON of the schema with key-values.
     */
    public async getSchema(entity: string) {
        try {
            return await this.api.fetch('?get=core_model_schema&entity=' + entity);
        }
        catch (response: any) {
            console.warn('request error', response);
        }
    }

    /**
     * @param new_schema - The new schema
     */
    public async updateSchema(new_schema: {}, eq_package: string, eq_class: string) {
        try {
            return await this.api.fetch('?do=config_save-model&part=class&entity=' + eq_package + "\\" + eq_class + '&payload=' + JSON.stringify(new_schema));
        }
        catch (response: any) {
            console.warn('request error', response);
        }
        console.log('#### schema updated', new_schema);
    }

    /**
     * Get all the domain's operators for each type.
     *
     * @returns An object with key-values.
     */
    public async getValidOperators() {
        try {
            return await this.api.fetch('?get=core_config_domain-operators');
        } catch (response: any) {
            console.warn('request error', response);
        }
    }

    /**
     * Return all the usages
     *
     * @returns a JSON with all the usages
     */
    public async getUsages() {
        try {
            return await this.api.fetch('?get=config_usage');
        } catch (response: any) {
            console.warn('request error', response);
        }
    }
}
