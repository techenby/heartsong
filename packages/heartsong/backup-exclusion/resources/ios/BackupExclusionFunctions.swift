import Foundation

// NativePHP keeps the SQLite database, Laravel storage, and logs under Application Support.
// Excluding the whole directory keeps every copy of the app's data on this device only.
enum BackupExclusionFunctions {

    static func applicationSupportURL() throws -> URL {
        let url = try FileManager.default.url(
            for: .applicationSupportDirectory,
            in: .userDomainMask,
            appropriateFor: nil,
            create: true
        )

        return url
    }

    static func isExcluded(_ url: URL) -> Bool {
        let values = try? url.resourceValues(forKeys: [.isExcludedFromBackupKey])

        return values?.isExcludedFromBackup ?? false
    }

    class Apply: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            var url = try BackupExclusionFunctions.applicationSupportURL()

            var values = URLResourceValues()
            values.isExcludedFromBackup = true
            try url.setResourceValues(values)

            return BridgeResponse.success(data: [
                "excluded": BackupExclusionFunctions.isExcluded(url),
                "path": url.path,
            ])
        }
    }

    class Status: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let url = try BackupExclusionFunctions.applicationSupportURL()

            return BridgeResponse.success(data: [
                "excluded": BackupExclusionFunctions.isExcluded(url),
                "path": url.path,
            ])
        }
    }
}
