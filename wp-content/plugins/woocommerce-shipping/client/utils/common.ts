import { camelCase, mapKeys, snakeCase } from 'lodash';
import { CamelCaseType, ShipmentRecord } from '../types';

export const camelCaseKeys = <
	T extends object,
	ReturnType = CamelCaseType< T >
>(
	obj: T
): ReturnType =>
	mapKeys< T >( obj, ( value, key ) => {
		return camelCase( key );
	} ) as ReturnType;

export const snakeCaseKeys = <
	T extends object,
	ReturnType = CamelCaseType< T >
>(
	obj: T
): ReturnType =>
	mapKeys< T >( obj, ( value, key ) => {
		return snakeCase( key );
	} ) as ReturnType;

export const removeShipmentFromKeys = < T >( data: ShipmentRecord< T > ) =>
	mapKeys( data, ( value, key ) => key.replace( 'shipment_', '' ) ?? key );

export const findClosestIndex = (
	index: number,
	indexes: number[],
	initial = 0
) =>
	indexes.reduce(
		( closest, current ) =>
			Math.abs( closest - index ) < Math.abs( closest - index )
				? current
				: closest,
		initial
	);

export const renderWhenDOMReady = ( callback: () => void ) => {
	if ( document.readyState === 'complete' ) {
		callback();
	} else {
		document.addEventListener( 'DOMContentLoaded', callback );
	}
};