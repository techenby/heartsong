import Foundation

// PHP doesn't read the system timezone, so the app asks for it over the bridge.
enum DeviceTimezoneFunctions {

    class Get: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            return BridgeResponse.success(data: [
                "timezone": TimeZone.current.identifier,
            ])
        }
    }
}
